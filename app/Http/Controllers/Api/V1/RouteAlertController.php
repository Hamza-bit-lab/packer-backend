<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRouteAlertRequest;
use App\Http\Resources\RouteAlertResource;
use App\Models\RouteAlert;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RouteAlertController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return RouteAlertResource::collection(
            $request->user()->routeAlerts()->latest()->get()
        );
    }

    public function store(StoreRouteAlertRequest $request): RouteAlertResource
    {
        $alert = $request->user()->routeAlerts()->firstOrCreate([
            'watch_type' => $request->validated('watch_type'),
            'from_city' => $request->validated('from_city'),
            'to_city' => $request->validated('to_city'),
        ]);

        return new RouteAlertResource($alert);
    }

    public function destroy(Request $request, RouteAlert $routeAlert): Response
    {
        abort_unless($request->user()->id === $routeAlert->user_id, 403);

        $routeAlert->delete();

        return response()->noContent();
    }
}
