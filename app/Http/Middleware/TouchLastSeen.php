<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TouchLastSeen
{
    /**
     * Records presence for the "online" / "last seen" indicator in chat.
     *
     * Throttled to once a minute per user rather than every request — the
     * UI only needs minute-level freshness, and writing on every API call
     * (the app polls messages every few seconds) would be a needless write
     * on practically every request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ($user->last_seen_at === null || $user->last_seen_at->lt(now()->subMinute()))) {
            $user->timestamps = false;
            $user->forceFill(['last_seen_at' => now()])->save();
        }

        return $next($request);
    }
}
