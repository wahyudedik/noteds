<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuditLogService;
use App\Services\RateLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('00-auth.auth.login');
    }

    /**
     * Handle an incoming authentication request with rate limiting & audit logging
     */
    public function store(LoginRequest $request, RateLimitService $rateLimiter, AuditLogService $auditLog): RedirectResponse
    {
        $email = $request->email;

        // Check rate limiting for login attempts (5 per 15 minutes)
        try {
            $rateLimiter->rateLimitLogin($email);
        } catch (\Exception $e) {
            // Log failed attempt
            $auditLog->logFailedLogin($email, $request->ip(), 'Rate limit exceeded');

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Terlalu banyak percobaan login. Silakan coba lagi nanti.']);
        }

        // Attempt authentication
        try {
            $request->authenticate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $auditLog->logFailedLogin($email, $request->ip(), 'Invalid credentials');
            throw $e;
        }

        $request->session()->regenerate();
        $user = Auth::user();

        // Verify account is active and not suspended (middleware already checks is_active)
        // Removed $user->status check because status column doesn't exist

        // Log successful login with IP, user agent, location
        $auditLog->logLogin(
            $user,
            $request->ip(),
            $request->userAgent(),
            $this->getLocation($request->ip())
        );

        // Redirect based on user role
        if ($user->hasRole('admin')) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session with audit logging
     */
    public function destroy(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            $auditLog->logLogout($user, $request->ip());
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Get user location from IP (basic implementation, can integrate with MaxMind GeoIP)
     */
    protected function getLocation(string $ip): ?string
    {
        // TODO: Integrate with MaxMind GeoIP2 for accurate location detection
        // For now, return null - but in production use:
        // $geoip = geoip()->getLocation($ip);
        // return $geoip->country . ', ' . $geoip->city;
        return null;
    }
}
