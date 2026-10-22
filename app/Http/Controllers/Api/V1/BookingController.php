<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\BookingStatus;
use App\Enums\Payer;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingStatusRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Good;
use App\Models\Trip;
use App\Notifications\BookingAccepted;
use App\Notifications\BookingArrived;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingCompleted;
use App\Notifications\BookingInTransit;
use App\Notifications\BookingRejected;
use App\Notifications\BookingRequested;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    private const RELATIONS = ['trip.user', 'good.user', 'sender', 'traveler'];

    public function index(Request $request): AnonymousResourceCollection
    {
        $bookings = Booking::query()
            ->where('sender_id', $request->user()->id)
            ->orWhere('traveler_id', $request->user()->id)
            ->with(self::RELATIONS)
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return BookingResource::collection($bookings);
    }

    public function store(StoreBookingRequest $request): BookingResource
    {
        $trip = Trip::findOrFail($request->validated('trip_id'));
        $good = Good::findOrFail($request->validated('good_id'));

        $booking = Booking::create([
            'trip_id' => $trip->id,
            'good_id' => $good->id,
            'sender_id' => $good->user_id,
            'traveler_id' => $trip->user_id,
            'status' => BookingStatus::Pending,
            'estimated_cost' => (float) $good->weight_kg * (float) $trip->rate_per_kg,
            'payer' => Payer::from($request->validated('payer') ?? Payer::Sender->value),
        ]);

        $trip->user->notify(new BookingRequested($booking));

        return new BookingResource($booking->load(self::RELATIONS));
    }

    public function show(Booking $booking): BookingResource
    {
        $this->authorize('view', $booking);

        return new BookingResource($booking->load(self::RELATIONS));
    }

    public function accept(Booking $booking): BookingResource
    {
        $this->authorize('respond', $booking);
        $this->ensureStatus($booking, BookingStatus::Pending);

        $booking->accept();
        $booking->sender->notify(new BookingAccepted($booking));

        return new BookingResource($booking->load(self::RELATIONS));
    }

    public function reject(Booking $booking): BookingResource
    {
        $this->authorize('respond', $booking);
        $this->ensureStatus($booking, BookingStatus::Pending);

        $booking->reject();
        $booking->sender->notify(new BookingRejected($booking));

        return new BookingResource($booking->load(self::RELATIONS));
    }

    public function markInTransit(Booking $booking): BookingResource
    {
        $this->authorize('respond', $booking);
        $this->ensureStatus($booking, BookingStatus::Accepted);

        $booking->markInTransit();
        $booking->sender->notify(new BookingInTransit($booking));

        return new BookingResource($booking->load(self::RELATIONS));
    }

    public function markArrived(Booking $booking): BookingResource
    {
        $this->authorize('respond', $booking);
        $this->ensureStatus($booking, BookingStatus::InTransit);

        $booking->markArrived();
        $booking->sender->notify(new BookingArrived($booking));

        return new BookingResource($booking->load(self::RELATIONS));
    }

    public function complete(UpdateBookingStatusRequest $request, Booking $booking): BookingResource
    {
        $this->ensureStatus($booking, BookingStatus::ArrivedAtDestination);

        $path = $request->file('photo')->store('delivery-proof-photos', 'uploads');
        $photoUrl = $request->getSchemeAndHttpHost().'/uploads/'.$path;

        $booking->complete($photoUrl);
        $booking->sender->notify(new BookingCompleted($booking));
        $booking->traveler->increment('completed_deliveries_count');

        return new BookingResource($booking->load(self::RELATIONS));
    }

    public function cancel(Request $request, Booking $booking): BookingResource
    {
        $this->authorize('cancel', $booking);
        $this->ensureStatus($booking, BookingStatus::Pending);

        $booking->cancel();

        $canceller = $request->user();
        $otherParty = $canceller->id === $booking->sender_id ? $booking->traveler : $booking->sender;
        $otherParty->notify(new BookingCancelled($booking, $canceller));

        return new BookingResource($booking->load(self::RELATIONS));
    }

    private function ensureStatus(Booking $booking, BookingStatus $expected): void
    {
        if ($booking->status !== $expected) {
            throw ValidationException::withMessages([
                'status' => "This booking must be {$expected->value} for that action.",
            ]);
        }
    }
}
