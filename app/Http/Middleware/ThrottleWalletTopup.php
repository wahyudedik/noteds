<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ThrottleWalletTopup
{
    /**
     * Handle an incoming request.
     *
     * Rate limit wallet top-up to prevent abuse/DDoS:
     * - Max 5 top-up requests per minute per user
     * - Max 20 per hour per user
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        // Rate limit per minute (5 requests)
        $minuteKey = 'topup-minute:' . $user->id;
        $maxPerMinute = 5;

        if (RateLimiter::tooManyAttempts($minuteKey, $maxPerMinute)) {
            $seconds = RateLimiter::availableIn($minuteKey);
            return redirect()->route('wallet.index')
                ->with('error', "Too many top-up attempts. Please wait {$seconds} seconds before trying again.");
        }

        RateLimiter::hit($minuteKey, 60); // 60 seconds = 1 minute

        // Rate limit per hour (20 requests)
        $hourKey = 'topup-hour:' . $user->id;
        $maxPerHour = 20;

        if (RateLimiter::tooManyAttempts($hourKey, $maxPerHour)) {
            $minutes = ceil(RateLimiter::availableIn($hourKey) / 60);
            return redirect()->route('wallet.index')
                ->with('error', "Maximum top-up requests reached. Please try again later.");
        }

        RateLimiter::hit($hourKey, 3600); // 3600 seconds = 1 hour

        return $next($request);
    }
}
