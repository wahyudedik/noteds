<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FixViteUrls
{
    /**
     * Handle an incoming request.
     *
     * Replace HTTPS Vite URLs with HTTP in development to prevent SSL errors
     * when the app is served over HTTPS but Vite dev server is on HTTP.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Force HTTP scheme for Vite asset generation in development
        if (config('app.env') === 'local' && $request->secure()) {
            // Temporarily modify request to use HTTP for Vite
            $request->server->set('HTTPS', 'off');
            $request->headers->set('X-Forwarded-Proto', 'http');
        }

        $response = $next($request);

        // Only process HTML responses in local environment
        if (config('app.env') !== 'local') {
            return $response;
        }

        // Try to get content - handle different response types
        $content = null;

        if (method_exists($response, 'getContent')) {
            $content = $response->getContent();
        } elseif ($response instanceof \Symfony\Component\HttpFoundation\Response) {
            $content = $response->getContent();
        }

        // Check if content is false (streamed response) or empty
        if ($content === false || empty($content) || !is_string($content)) {
            return $response;
        }

        // Get the hostname
        $host = $request->getHost();

        // Check if content contains Vite URLs (more lenient check)
        if (!str_contains($content, ':5173')) {
            return $response;
        }

        // Replace HTTPS Vite URLs with HTTP
        // This handles all Vite asset URLs including @vite/client, CSS, and JS files
        $oldContent = $content;

        // Replace all variations of HTTPS Vite URLs - use more aggressive pattern
        $content = preg_replace(
            '#https://' . preg_quote($host, '#') . ':5173#',
            'http://' . $host . ':5173',
            $content
        );

        // Always update if we found Vite URLs
        if ($content !== $oldContent) {
            if (method_exists($response, 'setContent')) {
                $response->setContent($content);
            } elseif ($response instanceof \Symfony\Component\HttpFoundation\Response) {
                $response->setContent($content);
            }
        }

        return $response;
    }
}
