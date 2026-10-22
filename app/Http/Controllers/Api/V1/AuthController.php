<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Resources\MeResource;
use App\Models\User;
use App\Notifications\EmailVerificationOtp;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone_number' => $request->validated('phone_number'),
            'country' => $request->validated('country'),
            'password' => Hash::make($request->validated('password')),
        ]);
        $user->refresh();

        $code = $user->generateEmailVerificationCode();
        $user->notify(new EmailVerificationOtp($code));

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'user' => new MeResource($user),
            'token' => $token,
        ], 201);
    }

    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Your email is already verified.']);
        }

        if (! $user->verifyEmailWithCode($request->validated('code'))) {
            throw ValidationException::withMessages(['code' => 'That code is incorrect or has expired.']);
        }

        return response()->json(['message' => 'Email verified.', 'user' => new MeResource($user)]);
    }

    public function resendVerificationEmail(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Your email is already verified.']);
        }

        $code = $request->user()->generateEmailVerificationCode();
        $request->user()->notify(new EmailVerificationOtp($code));

        return response()->json(['message' => 'Verification code sent.']);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            throw new AuthenticationException('These credentials do not match our records.');
        }

        if ($user->is_banned) {
            throw new AuthenticationException('Your account has been suspended.');
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'user' => new MeResource($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request): MeResource
    {
        return new MeResource($request->user());
    }
}
