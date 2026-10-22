<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviceTokenRequest;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    /**
     * Register (or move) this device's push token to the authenticated
     * user. A token is unique per device install, not per user — if it was
     * previously registered to someone else (e.g. a different account
     * logged out on this same phone), it's reassigned rather than
     * duplicated, so the old owner stops receiving this device's pushes.
     */
    public function store(StoreDeviceTokenRequest $request): JsonResponse
    {
        DeviceToken::updateOrCreate(
            ['token' => $request->validated('token')],
            ['user_id' => $request->user()->id, 'platform' => $request->validated('platform')],
        );

        return response()->json(['message' => 'Device registered.'], 201);
    }

    /**
     * Unregister this device's token — called on logout, so a signed-out
     * session stops receiving pushes meant for whoever logs in next.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['token' => ['required', 'string']]);

        DeviceToken::where('token', $request->string('token'))
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['message' => 'Device unregistered.']);
    }
}
