<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Get intended URL or default to home
        $intended = $request->session()->pull('url.intended', route('home', absolute: false));
        
        // Prevent redirect to post detail pages - always redirect to home after login
        // This prevents users from being stuck on a specific post page
        if (str_contains($intended, '/posts/')) {
            // If intended URL is a post detail page, ignore it and go to home
            return redirect()->route('home');
        }
        
        // Ensure intended URL is not a post route with invalid ID
        // If intended is /home, use it; otherwise check if it's a valid route
        if ($intended === route('home', absolute: false) || str_starts_with($intended, '/home')) {
            return redirect()->route('home');
        }
        
        // If intended is dashboard, use it
        if (str_contains($intended, '/dashboard')) {
            return redirect()->route('dashboard');
        }
        
        // Default to home
        return redirect()->route('home');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
