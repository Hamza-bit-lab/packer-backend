<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\RouteAlertType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Http\Resources\TripResource;
use App\Models\Good;
use App\Models\RouteAlert;
use App\Models\Trip;
use App\Notifications\MatchingRouteListing;
use App\Notifications\RouteAlertMatched;
use App\Support\ListingRemoval;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TripController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $trips = Trip::query()
            ->with('user')
            ->where('departure_date_time', '>=', now())
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->string('from_city')->isNotEmpty(), fn ($query) => $query->where('from_city', 'like', "%{$request->string('from_city')}%"))
            ->when($request->string('to_city')->isNotEmpty(), fn ($query) => $query->where('to_city', 'like', "%{$request->string('to_city')}%"))
            ->when($request->filled('date_from'), fn ($query) => $query->where('departure_date_time', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->where('departure_date_time', '<=', $request->date('date_to')))
            ->when($request->filled('min_weight'), fn ($query) => $query->where('max_weight_kg', '>=', $request->float('min_weight')))
            ->when($request->filled('max_weight'), fn ($query) => $query->where('max_weight_kg', '<=', $request->float('max_weight')))
            // This route is public (guests can browse), so it sits outside the
            // auth:sanctum middleware group — $request->user() would only ever
            // resolve the default guard, which that middleware is what normally
            // sets. Naming the guard explicitly resolves the token regardless.
            ->when($request->user('sanctum'), fn ($query) => $query->whereNotIn('user_id', $request->user('sanctum')->blockedUserIds()))
            ->when($request->string('sort')->toString(), function ($query, string $sort) {
                match ($sort) {
                    'price_low' => $query->orderBy('rate_per_kg'),
                    'price_high' => $query->orderByDesc('rate_per_kg'),
                    'rating' => $query->join('users', 'users.id', '=', 'trips.user_id')
                        ->orderByDesc('users.average_rating')
                        ->select('trips.*'),
                    'experience' => $query->join('users', 'users.id', '=', 'trips.user_id')
                        ->orderByDesc('users.completed_deliveries_count')
                        ->select('trips.*'),
                    default => $query->latest('departure_date_time'),
                };
            }, fn ($query) => $query->latest('departure_date_time'))
            ->paginate($request->integer('per_page', 15));

        return TripResource::collection($trips);
    }

    public function store(StoreTripRequest $request): TripResource
    {
        $trip = $request->user()->trips()->create([
            ...$request->validated(),
            'terms_accepted_at' => now(),
        ]);

        if ($request->hasFile('photo')) {
            $trip->update(['photo_url' => $this->storePhoto($request)]);
        }

        $this->notifyMatchingRouteAlerts($trip);
        $this->notifyMatchingSenders($trip);

        return new TripResource($trip->load('user'));
    }

    private function notifyMatchingRouteAlerts(Trip $trip): void
    {
        RouteAlert::query()
            ->where('watch_type', RouteAlertType::Travelers)
            ->where('from_city', $trip->from_city)
            ->where('to_city', $trip->to_city)
            ->where('user_id', '!=', $trip->user_id)
            ->with('user')
            ->each(fn (RouteAlert $alert) => $alert->user->notify(new RouteAlertMatched($trip)));
    }

    /**
     * Tells anyone who already needs to send something on this exact route
     * about the brand-new trip that can carry it — without them having to
     * have set up a route alert first, since they've already shown intent
     * by posting the item.
     */
    private function notifyMatchingSenders(Trip $trip): void
    {
        Good::query()
            ->where('from_city', $trip->from_city)
            ->where('to_city', $trip->to_city)
            ->where('user_id', '!=', $trip->user_id)
            ->with('user')
            ->get()
            ->unique('user_id')
            ->each(fn (Good $good) => $good->user->notify(new MatchingRouteListing($trip)));
    }

    public function show(Request $request, Trip $trip): TripResource
    {
        // Only counts other people opening this trip — the traveler
        // previewing their own post shouldn't inflate their own view count.
        // This route is public (guests can browse), so it sits outside the
        // auth:sanctum middleware group — $request->user() would only ever
        // resolve the default guard, which that middleware is what normally
        // sets. Naming the guard explicitly resolves the token regardless.
        if ($request->user('sanctum')?->id !== $trip->user_id) {
            $trip->increment('views_count');
        }

        return new TripResource($trip->load('user'));
    }

    public function update(UpdateTripRequest $request, Trip $trip): TripResource
    {
        $trip->update($request->validated());

        if ($request->hasFile('photo')) {
            $trip->update(['photo_url' => $this->storePhoto($request)]);
        }

        return new TripResource($trip->load('user'));
    }

    public function destroy(Request $request, Trip $trip): Response
    {
        $this->authorize('delete', $trip);

        ListingRemoval::deleteTrip($trip, $request->user());

        return response()->noContent();
    }

    private function storePhoto(Request $request): string
    {
        $path = $request->file('photo')->store('trip-photos', 'uploads');

        return $request->getSchemeAndHttpHost().'/uploads/'.$path;
    }
}
