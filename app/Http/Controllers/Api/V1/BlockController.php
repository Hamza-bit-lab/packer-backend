<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class BlockController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $blocked = User::query()
            ->whereIn('id', $request->user()->blocksMade()->pluck('blocked_user_id'))
            ->get();

        return UserResource::collection($blocked);
    }

    public function store(Request $request, User $user): JsonResponse
    {
        if ($request->user()->id === $user->id) {
            throw ValidationException::withMessages(['user' => 'You cannot block yourself.']);
        }

        $request->user()->blocksMade()->firstOrCreate(['blocked_user_id' => $user->id]);

        return response()->json(['message' => "{$user->name} has been blocked."], 201);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $request->user()->blocksMade()->where('blocked_user_id', $user->id)->delete();

        return response()->json(['message' => "{$user->name} has been unblocked."]);
    }
}
