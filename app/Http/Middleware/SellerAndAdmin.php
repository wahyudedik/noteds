<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SellerAndAdmin
{
    /**
     * Handle an incoming request.
     * Allow both sellers and admin to access features.
     * Admin can access for audit and oversight purposes.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();

        // Allow sellers and admin to access
        if ($user->role === 'seller' || $user->hasRole('admin')) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses ke fitur ini.');
    }
}
