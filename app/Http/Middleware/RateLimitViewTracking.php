<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitViewTracking
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $key = 'view_tracking:' . ($user ? $user->id : $request->ip());

        // Rate limit: 10 requests per hour per user
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'message' => 'Too many view tracking requests. Please try again in ' . ceil($seconds / 60) . ' minutes.',
            ], 429)->withHeaders([
                'Retry-After' => $seconds,
                'X-RateLimit-Limit' => 10,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        RateLimiter::hit($key, 3600); // 1 hour

        $response = $next($request);

        $remaining = RateLimiter::remaining($key, 10);
        
        return $response->withHeaders([
            'X-RateLimit-Limit' => 10,
            'X-RateLimit-Remaining' => $remaining,
        ]);
    }
}

