<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Security Headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Remove X-Powered-By header
        $response->headers->remove('X-Powered-By');

        // HSTS (HTTP Strict Transport Security) - only for HTTPS and NOT in local development
        // Disable HSTS in local to prevent Chrome from forcing HTTPS
        if ($request->secure() && !app()->environment('local')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // Content Security Policy (CSP) - configurable via env
        if (config('security.csp.enabled', true)) {
            $csp = $this->buildCSP();
            $response->headers->set('Content-Security-Policy', $csp);
        }

        // Permissions Policy (formerly Feature Policy)
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=(), payment=()'
        );

        return $response;
    }

    /**
     * Build Content Security Policy string
     */
    private function buildCSP(): string
    {
        $directives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' " . $this->getAllowedScriptSources(),
            "style-src 'self' 'unsafe-inline' " . $this->getAllowedStyleSources(),
            "img-src 'self' data: blob: " . $this->getAllowedImageSources(),
            "font-src 'self' data: https://fonts.bunny.net",
            "connect-src 'self' " . $this->getAllowedConnectSources(),
            "frame-src 'self' " . $this->getAllowedFrameSources(),
            "media-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ];

        // Only add upgrade-insecure-requests in production (not in local development)
        // This prevents Chrome from forcing HTTPS for Vite dev server
        if (!app()->environment('local')) {
            $directives[] = "upgrade-insecure-requests";
        }

        return implode('; ', $directives);
    }

    /**
     * Get allowed script sources (CDN, external services)
     */
    private function getAllowedScriptSources(): string
    {
        $sources = [];

        // Midtrans Snap.js
        if (config('services.midtrans.is_production', false)) {
            $sources[] = 'https://app.midtrans.com';
        } else {
            $sources[] = 'https://app.sandbox.midtrans.com';
        }

        // Iconify Icons CDN
        $sources[] = 'https://code.iconify.design';

        // Quill.js CDN (Rich Text Editor)
        $sources[] = 'https://cdn.quilljs.com';

        // Cloudflare Insights & Rocket Loader
        $sources[] = 'https://static.cloudflareinsights.com';
        $sources[] = 'https://cdn.jsdelivr.net';

        // cdnjs.cloudflare.com (Prism, PDF.js, Model Viewer, etc.)
        $sources[] = 'https://cdnjs.cloudflare.com';
        $sources[] = 'https://ajax.googleapis.com';

        // Google Tag Manager & Google Analytics
        $sources[] = 'https://www.googletagmanager.com';
        $sources[] = 'https://www.google-analytics.com';
        $sources[] = 'https://*.google-analytics.com';

        // CDN URLs
        if ($cdnUrl = config('filesystems.disks.public.url')) {
            $sources[] = parse_url($cdnUrl, PHP_URL_HOST);
        }

        // Vite HMR (development only)
        if (app()->environment('local')) {
            $sources[] = 'http://localhost:5173';
            // Also allow Vite on the same hostname (for Herd/Valet)
            $host = request()->getHost();
            $sources[] = "https://{$host}:5173";
            $sources[] = "http://{$host}:5173";
        }

        return implode(' ', $sources);
    }

    /**
     * Get allowed style sources
     */
    private function getAllowedStyleSources(): string
    {
        $sources = [];

        // Bunny Fonts
        $sources[] = 'https://fonts.bunny.net';

        // Quill.js CDN (Rich Text Editor CSS)
        $sources[] = 'https://cdn.quilljs.com';

        // cdnjs.cloudflare.com (Prism syntax highlighting CSS)
        $sources[] = 'https://cdnjs.cloudflare.com';

        // CDN URLs
        if ($cdnUrl = config('filesystems.disks.public.url')) {
            $sources[] = parse_url($cdnUrl, PHP_URL_HOST);
        }

        // Vite HMR (development only)
        if (app()->environment('local')) {
            $host = request()->getHost();
            $sources[] = "https://{$host}:5173";
            $sources[] = "http://{$host}:5173";
        }

        return implode(' ', $sources);
    }

    /**
     * Get allowed frame sources (iframes)
     */
    private function getAllowedFrameSources(): string
    {
        $sources = [];

        // Midtrans Snap iframe
        if (config('services.midtrans.is_production', false)) {
            $sources[] = 'https://app.midtrans.com';
        } else {
            $sources[] = 'https://app.sandbox.midtrans.com';
        }

        return implode(' ', $sources);
    }

    /**
     * Get allowed image sources
     */
    private function getAllowedImageSources(): string
    {
        $sources = [];

        // CDN URLs
        if ($cdnUrl = config('filesystems.disks.public.url')) {
            $sources[] = parse_url($cdnUrl, PHP_URL_HOST);
        }

        // Allow external images for avatars and notes (if using URLs)
        $sources[] = 'https:';

        return implode(' ', $sources);
    }

    /**
     * Get allowed connect sources (API endpoints)
     */
    private function getAllowedConnectSources(): string
    {
        $sources = [];

        // Midtrans API
        if (config('services.midtrans.is_production', false)) {
            $sources[] = 'https://api.midtrans.com';
        } else {
            $sources[] = 'https://api.sandbox.midtrans.com';
        }

        // Pusher/Ably for broadcasting
        if ($pusherKey = config('broadcasting.connections.pusher.key')) {
            $sources[] = 'https://*.pusher.com';
            $sources[] = 'wss://*.pusher.com';
        }

        // Cloudflare Insights & Analytics
        $sources[] = 'https://*.cloudflare.com';
        $sources[] = 'https://cloudflareinsights.com';
        $sources[] = 'https://*.cloudflareinsights.com';

        // Google Tag Manager & Analytics
        $sources[] = 'https://www.googletagmanager.com';
        $sources[] = 'https://www.google-analytics.com';
        $sources[] = 'https://*.google-analytics.com';

        // CDN URLs
        if ($cdnUrl = config('filesystems.disks.public.url')) {
            $sources[] = parse_url($cdnUrl, PHP_URL_HOST);
        }

        // Vite HMR WebSocket (development only)
        if (app()->environment('local')) {
            $sources[] = 'ws://localhost:5173';
            $sources[] = 'wss://localhost:5173';
            $host = request()->getHost();
            $sources[] = "ws://{$host}:5173";
            $sources[] = "wss://{$host}:5173";
        }

        return implode(' ', $sources);
    }
}
