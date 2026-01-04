<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsClipper
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Allow both brand and clipper roles (clipper middleware is for both roles)
        if (!$user->isBrand() && !$user->isClipper() && !$user->isAdmin()) {
            abort(403, 'You must be a brand or clipper to access this page.');
        }

        return $next($request);
    }
}
