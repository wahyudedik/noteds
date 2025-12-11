<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBuyerRole
{
    /**
     * Handle an incoming request.
     * Only buyers can access this route. Sellers are not allowed.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();

        // Admin can access everything
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // Only buyers can access (use hasRole for Spatie permission compatibility)
        if (!$user->hasRole('buyer')) {
            return redirect()->back()->with('error', 'Fitur ini hanya tersedia untuk Buyer. Seller tidak dapat membeli note. Jika ingin membeli, silakan buat akun Buyer dengan email berbeda.');
        }

        return $next($request);
    }
}
