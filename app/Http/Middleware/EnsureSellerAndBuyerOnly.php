<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerAndBuyerOnly
{
    /**
     * Handle an incoming request.
     * Only sellers and buyers can access this route. Admin is not allowed.
     * This is used for public features like Leaderboards.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();

        // Block admin users from accessing this route
        if ($user->hasRole('admin')) {
            return redirect('/admin/dashboard')->with('error', 'Fitur ini hanya untuk seller dan buyer.');
        }

        return $next($request);
    }
}
