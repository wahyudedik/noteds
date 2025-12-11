<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotAdminAffiliate
{
    /**
     * Handle an incoming request.
     * Affiliate features are accessible to sellers, buyers, and admin (for audit).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();

        // Allow sellers, buyers, and admin to access affiliate features
        // Admin can access for audit and oversight purposes
        if ($user->role === 'seller' || $user->role === 'buyer' || $user->hasRole('admin')) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses ke fitur affiliate.');
    }
}
