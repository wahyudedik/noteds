<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerRole
{
    /**
     * Handle an incoming request.
     * Only sellers can access this route. Buyers are not allowed.
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

        // Only sellers can access
        if ($user->role !== 'seller') {
            return redirect()->back()->with('error', 'Fitur ini hanya tersedia untuk Seller. Buyer tidak dapat menjual note. Jika ingin menjual, silakan buat akun Seller dengan email berbeda.');
        }

        return $next($request);
    }
}
