<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserNotBanned
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->is_banned) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your account has been banned. Please contact support if you believe this is an error.',
                    'ban_reason' => auth()->user()->ban_reason,
                ], 403);
            }

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Your account has been banned. Please contact support if you believe this is an error.');
        }

        return $next($request);
    }
}

