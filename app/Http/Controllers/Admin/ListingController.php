<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Good;
use App\Models\Trip;
use App\Support\ListingRemoval;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Moderation view over every trip and item — an admin can remove one that
 * breaks the rules, and anyone with a live booking on it is told.
 */
class ListingController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->query('type') === 'goods' ? 'goods' : 'trips';
        $search = trim((string) $request->query('search', ''));

        $model = $type === 'goods' ? Good::class : Trip::class;

        $listings = $model::query()
            ->with('user')
            ->withCount('bookings')
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('from_city', 'like', "%{$search}%")
                ->orWhere('to_city', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.listings.index', ['listings' => $listings, 'type' => $type, 'search' => $search]);
    }

    public function destroyTrip(Request $request, Trip $trip): RedirectResponse
    {
        ListingRemoval::deleteTrip($trip, $request->user());

        return back()->with('status', "Trip #{$trip->id} removed. The owner and anyone with a live booking were notified.");
    }

    public function destroyGood(Request $request, Good $good): RedirectResponse
    {
        ListingRemoval::deleteGood($good, $request->user());

        return back()->with('status', "Item #{$good->id} removed. The owner and anyone with a live booking were notified.");
    }
}
