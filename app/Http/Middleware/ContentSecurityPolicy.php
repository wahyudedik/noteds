<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://app.sandbox.midtrans.com https://cdn.quilljs.com https://unpkg.com",
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://cdn.quilljs.com https://cdn.jsdelivr.net",
            "img-src 'self' data: https: blob:",
            "font-src 'self' https://fonts.bunny.net data:",
            "connect-src 'self' https://api.sandbox.midtrans.com https://*.cloudflare.com https://cloudflareinsights.com https://cdn.jsdelivr.net",
            "frame-src 'self' https://app.sandbox.midtrans.com",
            "object-src 'none'",
            "base-uri 'self'",
        ];

        if (config('app.env') === 'local') {
            $csp[] = "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://noteds.test:5173 https://noteds.test:5173 https://cdn.jsdelivr.net https://app.sandbox.midtrans.com https://cdn.quilljs.com https://unpkg.com";
            $csp[] = "style-src 'self' 'unsafe-inline' http://noteds.test:5173 https://noteds.test:5173 https://fonts.bunny.net https://cdn.quilljs.com https://cdn.jsdelivr.net";
            $csp[] = "connect-src 'self' http://noteds.test:5173 https://noteds.test:5173 https://api.sandbox.midtrans.com https://*.cloudflare.com https://cloudflareinsights.com https://cdn.jsdelivr.net ws://localhost:5173 wss://localhost:5173 ws://noteds.test:5173 wss://noteds.test:5173";
        }

        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        return $response;
    }
}
