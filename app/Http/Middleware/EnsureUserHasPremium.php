<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPremium
{
    /**
     * Handle an incoming request.
     * 
     * Ensure the user has an active premium subscription.
     * Admin users always have access.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if user is authenticated
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                ], 401);
            }

            return redirect()->route('login');
        }

        // Admin users always have access
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // Check if user has premium subscription
        if (!$user->hasPremium()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This feature requires a Premium subscription. Please upgrade to access advanced AI Memory Platform features.',
                    'upgrade_url' => route('subscription.create'),
                ], 403);
            }

            return redirect()
                ->route('subscription.create')
                ->with('error', 'This feature requires a Premium subscription. Please upgrade to access advanced AI Memory Platform features.');
        }

        return $next($request);
    }
}

