<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerOnly
{
    /**
     * Handle an incoming request.
     * Only sellers can access this route. Buyers and Admin are not allowed.
     * This is used for seller-specific features like Share Analytics and Share Leaderboard.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();

        // Deny admin access
        if ($user->hasRole('admin')) {
            abort(403, 'Admin tidak dapat mengakses fitur ini. Fitur ini hanya tersedia untuk Seller.');
        }

        // Only sellers can access (use hasRole for Spatie permission compatibility)
        if (!$user->hasRole('seller')) {
            return redirect()->back()->with('error', 'Fitur ini hanya tersedia untuk Seller. Jika ingin menjual, silakan buat akun Seller dengan email berbeda.');
        }

        // Require identity verification for sellers
        if (($user->verification_status ?? 'pending') !== 'approved') {
            return redirect()->back()->with('error', 'Akun Anda belum terverifikasi. Silakan tunggu verifikasi admin setelah mengunggah KTP dan foto wajah saat pendaftaran.');
        }

        return $next($request);
    }
}
