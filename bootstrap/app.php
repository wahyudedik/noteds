<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'creator' => \App\Http\Middleware\EnsureUserIsCreator::class,
            'clipper' => \App\Http\Middleware\EnsureUserIsClipper::class,
            'not_banned' => \App\Http\Middleware\EnsureUserNotBanned::class,
            'rate_limit.view_tracking' => \App\Http\Middleware\RateLimitViewTracking::class,
            'rate_limit.clipper_api' => \App\Http\Middleware\RateLimitClipperApi::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            if ($request->expectsJson() || $request->is('marketplace/*')) {
                return response()->json([
                    'message' => 'File terlalu besar. Ukuran maksimal: 50MB.',
                    'error' => 'post_too_large'
                ], 413);
            }
            return back()->withErrors([
                'file_download' => 'File terlalu besar. Ukuran maksimal: 50MB.'
            ])->withInput();
        });

        // Handle 429 Too Many Requests
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) {
            // Detect action from route or request
            $action = 'request';
            $route = $request->route();
            $path = $request->path();
            $method = $request->method();

            // Detect action based on route name or path
            if ($route) {
                $routeName = $route->getName() ?? '';
                $lowerPath = strtolower($path);
                $lowerRouteName = strtolower($routeName);
                
                if (str_contains($lowerRouteName, 'bookmark') || str_contains($lowerPath, 'bookmark')) {
                    $action = 'bookmark';
                } elseif (str_contains($lowerRouteName, 'vote') || str_contains($lowerPath, 'vote')) {
                    $action = 'like';
                } elseif (str_contains($lowerRouteName, 'comment') || str_contains($lowerPath, 'comment')) {
                    $action = 'comment';
                } elseif (($lowerRouteName === 'posts.store' || str_contains($lowerPath, '/posts')) && $method === 'POST' && !str_contains($lowerPath, 'bookmark') && !str_contains($lowerPath, 'vote')) {
                    $action = 'post';
                } elseif (str_contains($lowerRouteName, 'register') || str_contains($lowerPath, 'register')) {
                    $action = 'register';
                } elseif (str_contains($lowerRouteName, 'login') || str_contains($lowerPath, 'login')) {
                    $action = 'login';
                } elseif (str_contains($lowerRouteName, 'search') || str_contains($lowerPath, 'search')) {
                    $action = 'search';
                }
            }

            // Get retry after seconds from headers
            $retryAfter = null;
            if ($e->getHeaders() && isset($e->getHeaders()['Retry-After'])) {
                $retryAfter = (int) $e->getHeaders()['Retry-After'];
            } elseif ($e->getHeaders() && isset($e->getHeaders()['X-RateLimit-Reset'])) {
                $resetTime = (int) $e->getHeaders()['X-RateLimit-Reset'];
                $retryAfter = max(0, $resetTime - time());
            }

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Terlalu banyak request. Silakan tunggu sebentar.',
                    'error' => 'too_many_requests',
                    'action' => $action,
                    'retry_after' => $retryAfter,
                ], 429);
            }

            // For Inertia requests, render error page
            return \Inertia\Inertia::render('Errors/429', [
                'action' => $action,
                'retryAfter' => $retryAfter,
            ])->toResponse($request)->setStatusCode(429);
        });
    })->create();
