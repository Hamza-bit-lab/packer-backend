<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotBanned
{
    /**
     * Handle an incoming request.
     *
     * A ban already deletes the user's tokens (see User::ban()), so this is
     * a defensive second check in case a token was issued and cached
     * client-side before the ban happened to be processed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->is_banned) {
            $user->currentAccessToken()?->delete();

            abort(403, 'Your account has been suspended.');
        }

        return $next($request);
    }
}
