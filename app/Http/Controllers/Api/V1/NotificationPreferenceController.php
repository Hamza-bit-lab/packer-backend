<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateNotificationPreferencesRequest;
use App\Http\Resources\MeResource;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function show(Request $request): MeResource
    {
        return new MeResource($request->user());
    }

    public function update(UpdateNotificationPreferencesRequest $request): MeResource
    {
        $user = $request->user();
        $user->fill($request->safe()->only(['notify_bookings', 'notify_messages', 'notify_route_alerts']));
        $user->save();

        return new MeResource($user);
    }
}
