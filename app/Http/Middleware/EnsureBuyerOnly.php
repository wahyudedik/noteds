<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBuyerOnly
{
    /**
     * Handle an incoming request.
     * Only buyers can access this route. Sellers and Admin are not allowed.
     * This is used for buyer-specific features like Collections.
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
            abort(403, 'Admin tidak dapat mengakses fitur ini. Fitur ini hanya tersedia untuk Buyer.');
        }

        // Only buyers can access
        if ($user->role !== 'buyer') {
            return redirect()->back()->with('error', 'Fitur ini hanya tersedia untuk Buyer. Seller tidak dapat menggunakan fitur Collections. Jika ingin membeli catatan, silakan buat akun Buyer dengan email berbeda.');
        }

        return $next($request);
    }
}
