<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for authenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        try {
            $user = Auth::user();

            if ($user && (! $user->is_active || $user->isSuspended())) {
                \Log::warning('EnsureUserIsActive blocked login', [
                    'user_id' => $user->id ?? null,
                    'email' => $user->email ?? null,
                    'is_active' => $user->is_active ?? null,
                    'suspended_at' => $user->suspended_at ?? null,
                ]);
                Auth::guard('web')->logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = $user->isSuspended()
                    ? __('auth.suspended')
                    : __('auth.inactive');

                return redirect()->route('login')->withErrors([
                    'email' => $message,
                ]);
            }
        } catch (\Exception $e) {
            // Log error but don't break the request
            \Log::error('Error in EnsureUserIsActive middleware', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $next($request);
    }
}
