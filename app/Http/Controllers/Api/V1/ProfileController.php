<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\MeResource;
use App\Http\Resources\RatingResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\ListingRemoval;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProfileController extends Controller
{
    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function ratings(Request $request, User $user): AnonymousResourceCollection
    {
        $ratings = $user->ratingsReceived()
            ->with(['rater', 'ratee'])
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return RatingResource::collection($ratings);
    }

    public function update(UpdateProfileRequest $request): MeResource
    {
        $user = $request->user();

        $user->fill($request->safe()->only(['name', 'phone_number', 'country']));

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile-pictures', 'uploads');
            // Built from the current request's host (not config('app.url')) so the URL is
            // reachable from whichever host the client actually used to reach the API —
            // e.g. a phone hitting the LAN IP would get a broken "packer.test" URL otherwise.
            $user->profile_pic_url = $request->getSchemeAndHttpHost().'/uploads/'.$path;
        }

        $user->save();

        return new MeResource($user);
    }

    /**
     * Permanent, in-app account deletion — gated on the account password so
     * a stolen/unattended phone can't wipe someone's account in one tap.
     */
    public function destroy(DeleteAccountRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages(['password' => 'That password is incorrect.']);
        }

        if ($user->is_admin) {
            throw ValidationException::withMessages(['password' => 'Admin accounts cannot be deleted from the app.']);
        }

        ListingRemoval::deleteAccount($user);

        return response()->json(['message' => 'Your account has been deleted.']);
    }
}
