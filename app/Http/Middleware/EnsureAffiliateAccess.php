<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAffiliateAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Allow only sellers and buyers
        if ($user && ($user->role === 'seller' || $user->role === 'buyer')) {
            return $next($request);
        }

        // Deny access to admin and others
        abort(403, 'Unauthorized access to affiliate features.');
    }
}
