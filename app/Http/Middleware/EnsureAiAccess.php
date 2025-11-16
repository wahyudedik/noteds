<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAiAccess
{
    /**
     * Handle an incoming request.
     * Admin can access all AI features without premium.
     * Seller and Buyer need premium subscription.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();
        
        // Admin can access everything without premium
        if ($user->hasRole('admin') || $user->hasRole('super_admin')) {
            return $next($request);
        }

        // Seller and Buyer need premium subscription
        if (!$user->hasPremium()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fitur AI ini memerlukan subscription premium. Silakan upgrade untuk menggunakan fitur ini.',
                    'requires_premium' => true,
                ], 403);
            }

            return redirect()->route('subscription.create')
                ->with('error', 'Fitur AI ini memerlukan subscription premium. Silakan upgrade untuk menggunakan fitur ini.');
        }

        return $next($request);
    }
}

