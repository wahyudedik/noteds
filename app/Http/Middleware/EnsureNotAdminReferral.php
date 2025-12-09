<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotAdminReferral
{
    /**
     * Handle an incoming request.
     * Referral features are only accessible to sellers and buyers, not admin.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();

        // Deny admin access to referral features
        if ($user->hasRole('admin')) {
            abort(403, 'Admin tidak dapat mengakses fitur referral. Fitur ini hanya tersedia untuk Seller dan Buyer.');
        }

        // Only sellers and buyers can access
        if ($user->role === 'seller' || $user->role === 'buyer') {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses ke fitur referral.');
    }
}
