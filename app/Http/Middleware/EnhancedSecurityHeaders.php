<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enhanced Security Headers Middleware
 * Add comprehensive security headers untuk protect against common attacks
 */
class EnhancedSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking
        $response->header('X-Frame-Options', 'DENY');

        // Prevent MIME sniffing
        $response->header('X-Content-Type-Options', 'nosniff');

        // Enable XSS protection
        $response->header('X-XSS-Protection', '1; mode=block');

        // Content Security Policy (prevent inline scripts, only allow from same origin)
        $response->header(
            'Content-Security-Policy',
            "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' cdn.jsdelivr.net; " .
                "style-src 'self' 'unsafe-inline'; " .
                "img-src 'self' data: https:; " .
                "font-src 'self' data: https:; " .
                "connect-src 'self' https:; " .
                "frame-ancestors 'none'; " .
                "base-uri 'self'; " .
                "form-action 'self'"
        );

        // Referrer Policy
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy
        $response->header(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=()'
        );

        // Strict Transport Security (HTTPS only)
        if (env('APP_ENV') === 'production') {
            $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Prevent caching of sensitive data
        if ($request->routeIs('profile.*', 'wallet.*', 'transaction.*', 'dispute.*')) {
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->header('Pragma', 'no-cache');
            $response->header('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
        }

        return $response;
    }
}
