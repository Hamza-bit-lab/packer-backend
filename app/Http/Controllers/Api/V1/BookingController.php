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
use App\Notifications\DeliveryOtp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    private const RELATIONS = ['trip.user', 'good.user', 'sender', 'traveler'];

    private const OTP_TTL_MINUTES = 15;

    /** Wrong guesses allowed before the code must be resent. */
    private const OTP_MAX_ATTEMPTS = 5;

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

    /**
     * Emails a 6-digit code to the good's receiver — proof a real handoff
     * happened before a booking can be marked complete, without the
     * receiver needing a Packer account of their own.
     */
    public function sendDeliveryOtp(Booking $booking): JsonResponse
    {
        $this->authorize('respond', $booking);
        $this->ensureStatus($booking, BookingStatus::ArrivedAtDestination);

        $booking->loadMissing('good', 'sender');
        $code = (string) random_int(100000, 999999);

        $booking->update([
            'delivery_otp_hash' => Hash::make($code),
            'delivery_otp_created_at' => now(),
        ]);
        Cache::forget($this->otpAttemptsKey($booking));

        Notification::route('mail', $booking->good->receiver_email)
            ->notify(new DeliveryOtp($code, $booking->sender->name, $booking->good->item_description));

        return response()->json(['message' => 'A confirmation code has been emailed to the receiver.']);
    }

    public function complete(UpdateBookingStatusRequest $request, Booking $booking): BookingResource
    {
        $this->ensureStatus($booking, BookingStatus::ArrivedAtDestination);
        $this->verifyDeliveryOtp($booking, $request->validated('otp'));

        $path = $request->file('photo')->store('delivery-proof-photos', 'uploads');
        $photoUrl = $request->getSchemeAndHttpHost().'/uploads/'.$path;

        $booking->complete($photoUrl);
        $booking->sender->notify(new BookingCompleted($booking));
        $booking->traveler->increment('completed_deliveries_count');

        Cache::forget($this->otpAttemptsKey($booking));

        return new BookingResource($booking->load(self::RELATIONS));
    }

    private function verifyDeliveryOtp(Booking $booking, string $otp): void
    {
        $invalid = fn () => ValidationException::withMessages([
            'otp' => 'That code is incorrect or has expired.',
        ]);

        if ($booking->delivery_otp_hash === null
            || Carbon::parse($booking->delivery_otp_created_at)->addMinutes(self::OTP_TTL_MINUTES)->isPast()) {
            throw $invalid();
        }

        $attemptsKey = $this->otpAttemptsKey($booking);
        $attempts = (int) Cache::get($attemptsKey, 0);

        if ($attempts >= self::OTP_MAX_ATTEMPTS) {
            $booking->update(['delivery_otp_hash' => null, 'delivery_otp_created_at' => null]);
            throw $invalid();
        }

        if (! Hash::check($otp, $booking->delivery_otp_hash)) {
            Cache::put($attemptsKey, $attempts + 1, now()->addMinutes(self::OTP_TTL_MINUTES));
            throw $invalid();
        }
    }

    private function otpAttemptsKey(Booking $booking): string
    {
        return "delivery-otp-attempts:{$booking->id}";
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
