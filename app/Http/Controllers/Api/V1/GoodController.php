<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\RouteAlertType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGoodRequest;
use App\Http\Requests\UpdateGoodRequest;
use App\Http\Resources\GoodResource;
use App\Models\Good;
use App\Models\RouteAlert;
use App\Models\Trip;
use App\Notifications\MatchingRouteListing;
use App\Notifications\RouteAlertMatched;
use App\Support\ListingRemoval;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class GoodController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $goods = Good::query()
            ->with('user')
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->string('from_city')->isNotEmpty(), fn ($query) => $query->where('from_city', 'like', "%{$request->string('from_city')}%"))
            ->when($request->string('to_city')->isNotEmpty(), fn ($query) => $query->where('to_city', 'like', "%{$request->string('to_city')}%"))
            ->when($request->filled('min_weight'), fn ($query) => $query->where('weight_kg', '>=', $request->float('min_weight')))
            ->when($request->filled('max_weight'), fn ($query) => $query->where('weight_kg', '<=', $request->float('max_weight')))
            // This route is public (guests can browse), so it sits outside the
            // auth:sanctum middleware group — $request->user() would only ever
            // resolve the default guard, which that middleware is what normally
            // sets. Naming the guard explicitly resolves the token regardless.
            ->when($request->user('sanctum'), fn ($query) => $query->whereNotIn('user_id', $request->user('sanctum')->blockedUserIds()))
            ->when($request->string('sort')->toString(), function ($query, string $sort) {
                match ($sort) {
                    'price_low' => $query->orderBy('offered_reward'),
                    'price_high' => $query->orderByDesc('offered_reward'),
                    'rating' => $query->join('users', 'users.id', '=', 'goods.user_id')
                        ->orderByDesc('users.average_rating')
                        ->select('goods.*'),
                    default => $query->latest(),
                };
            }, fn ($query) => $query->latest())
            ->paginate($request->integer('per_page', 15));

        return GoodResource::collection($goods);
    }

    public function store(StoreGoodRequest $request): GoodResource
    {
        $good = $request->user()->goods()->create([
            ...$request->validated(),
            'terms_accepted_at' => now(),
        ]);

        if ($request->hasFile('photo')) {
            $good->update(['photo_url' => $this->storePhoto($request)]);
        }

        $this->notifyMatchingRouteAlerts($good);
        $this->notifyMatchingTravelers($good);

        return new GoodResource($good->load('user'));
    }

    private function notifyMatchingRouteAlerts(Good $good): void
    {
        RouteAlert::query()
            ->where('watch_type', RouteAlertType::Goods)
            ->where('from_city', $good->from_city)
            ->where('to_city', $good->to_city)
            ->where('user_id', '!=', $good->user_id)
            ->with('user')
            ->each(fn (RouteAlert $alert) => $alert->user->notify(new RouteAlertMatched($good)));
    }

    /**
     * Tells anyone already flying this exact route about a brand-new item
     * that needs sending on it — without them having to have set up a route
     * alert first, since they've already shown intent by posting the trip.
     */
    private function notifyMatchingTravelers(Good $good): void
    {
        Trip::query()
            ->where('from_city', $good->from_city)
            ->where('to_city', $good->to_city)
            ->where('user_id', '!=', $good->user_id)
            ->with('user')
            ->get()
            ->unique('user_id')
            ->each(fn (Trip $trip) => $trip->user->notify(new MatchingRouteListing($good)));
    }

    public function show(Request $request, Good $good): GoodResource
    {
        // Only counts other people opening this item — the sender
        // previewing their own post shouldn't inflate their own view count.
        // This route is public (guests can browse), so it sits outside the
        // auth:sanctum middleware group — $request->user() would only ever
        // resolve the default guard, which that middleware is what normally
        // sets. Naming the guard explicitly resolves the token regardless.
        if ($request->user('sanctum')?->id !== $good->user_id) {
            $good->increment('views_count');
        }

        return new GoodResource($good->load('user'));
    }

    public function update(UpdateGoodRequest $request, Good $good): GoodResource
    {
        $good->update($request->validated());

        if ($request->hasFile('photo')) {
            $good->update(['photo_url' => $this->storePhoto($request)]);
        }

        return new GoodResource($good->load('user'));
    }

    public function destroy(Request $request, Good $good): Response
    {
        $this->authorize('delete', $good);

        ListingRemoval::deleteGood($good, $request->user());

        return response()->noContent();
    }

    private function storePhoto(Request $request): string
    {
        $path = $request->file('photo')->store('good-photos', 'uploads');

        return $request->getSchemeAndHttpHost().'/uploads/'.$path;
    }
}
