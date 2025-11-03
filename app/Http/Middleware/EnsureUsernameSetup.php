<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUsernameSetup
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if user is not authenticated
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // Skip if user already has username
        if ($user->username) {
            return $next($request);
        }

        // Skip if user is trying to access setup username page or logout
        if ($request->routeIs('setup-username.*') || 
            $request->routeIs('logout') || 
            $request->is('logout')) {
            return $next($request);
        }

        // Redirect to setup username page
        return redirect()->route('setup-username.create')
            ->with('warning', 'Please setup your username to continue using Noteds.');
    }
}
