<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Notifications\PasswordResetOtp;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    private const CODE_TTL_MINUTES = 15;

    /** Wrong guesses allowed before the code is thrown away. */
    private const MAX_ATTEMPTS = 5;

    /**
     * Emails a 6-digit code. Always answers the same way whether or not the
     * address is registered, so this can't be used to find out who has an
     * account.
     */
    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if ($user !== null && ! $user->is_banned) {
            $code = (string) random_int(100000, 999999);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                ['token' => Hash::make($code), 'created_at' => now()],
            );
            Cache::forget($this->attemptsKey($user->email));

            $user->notify(new PasswordResetOtp($code));
        }

        return response()->json(['message' => 'If that email is registered, we sent a reset code to it.']);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $email = $request->validated('email');
        $invalid = fn () => ValidationException::withMessages(['code' => 'That code is incorrect or has expired.']);

        $row = DB::table('password_reset_tokens')->where('email', $email)->first();

        if ($row === null || Carbon::parse($row->created_at)->addMinutes(self::CODE_TTL_MINUTES)->isPast()) {
            throw $invalid();
        }

        $attemptsKey = $this->attemptsKey($email);
        $attempts = (int) Cache::get($attemptsKey, 0);

        if ($attempts >= self::MAX_ATTEMPTS) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            throw $invalid();
        }

        if (! Hash::check($request->validated('code'), $row->token)) {
            Cache::put($attemptsKey, $attempts + 1, now()->addMinutes(self::CODE_TTL_MINUTES));
            throw $invalid();
        }

        $user = User::where('email', $email)->first();

        if ($user === null) {
            throw $invalid();
        }

        $user->forceFill(['password' => $request->validated('password')])->save();
        $user->tokens()->delete();

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        Cache::forget($attemptsKey);

        return response()->json(['message' => 'Password updated. Please log in with your new password.']);
    }

    private function attemptsKey(string $email): string
    {
        return 'password-reset-attempts:'.strtolower($email);
    }
}
