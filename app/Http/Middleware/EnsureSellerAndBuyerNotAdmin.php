<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerAndBuyerNotAdmin
{
    /**
     * Handle an incoming request.
     * Only sellers and buyers can access this route. Admin is not allowed.
     * This middleware is used for buyer-seller communication features like Note Conversations.
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
            return redirect('/admin/dashboard')->with('error', 'Fitur ini hanya tersedia untuk Seller dan Buyer. Admin tidak dapat mengakses fitur messaging.');
        }

        // Only sellers and buyers can access
        if ($user->role !== 'seller' && $user->role !== 'buyer') {
            abort(403, 'Unauthorized. This feature is only available for Sellers and Buyers.');
        }

        return $next($request);
    }
}
