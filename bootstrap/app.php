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
        // Replace default CSRF middleware with custom one
        $middleware->validateCsrfTokens(except: []);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'not_banned' => \App\Http\Middleware\EnsureUserNotBanned::class,
            'block.viewer.export' => \App\Http\Middleware\BlockViewerExport::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle 413 Post Too Large
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'File terlalu besar. Ukuran maksimal: 50MB.',
                    'error' => 'post_too_large'
                ], 413);
            }
            
            if ($request->header('X-Inertia')) {
                return \Inertia\Inertia::render('Errors/413', [
                    'message' => 'File terlalu besar. Ukuran maksimal: 50MB.',
                ])->toResponse($request)->setStatusCode(413);
            }
            
            return back()->withErrors([
                'file_download' => 'File terlalu besar. Ukuran maksimal: 50MB.'
            ])->withInput();
        });

        // Handle 401 Unauthorized
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'error' => 'unauthenticated'
                ], 401);
            }

            if ($request->header('X-Inertia')) {
                return \Inertia\Inertia::render('Errors/401', [
                    'message' => 'Kamu perlu login untuk mengakses halaman ini.',
                ])->toResponse($request)->setStatusCode(401);
            }

            return redirect()->guest(route('login'));
        });

        // Handle 403 Forbidden
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'This action is unauthorized.',
                    'error' => 'unauthorized'
                ], 403);
            }

            if ($request->header('X-Inertia')) {
                return \Inertia\Inertia::render('Errors/403', [
                    'message' => $e->getMessage() ?: 'Kamu tidak memiliki izin untuk mengakses halaman ini.',
                ])->toResponse($request)->setStatusCode(403);
            }

            return back()->withErrors(['error' => $e->getMessage() ?: 'This action is unauthorized.']);
        });

        // Handle 404 Not Found
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Resource not found.',
                    'error' => 'not_found'
                ], 404);
            }

            if ($request->header('X-Inertia')) {
                return \Inertia\Inertia::render('Errors/404', [
                    'message' => 'Halaman yang kamu cari tidak ditemukan atau sudah dihapus.',
                ])->toResponse($request)->setStatusCode(404);
            }

            return response()->view('errors.404', [], 404);
        });

        // Handle CSRF Token Mismatch
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'CSRF token mismatch. Please refresh the page and try again.',
                    'error' => 'csrf_token_mismatch'
                ], 419);
            }

            if ($request->header('X-Inertia')) {
                return \Inertia\Inertia::render('Errors/419', [
                    'message' => 'Sesi Anda telah berakhir. Silakan refresh halaman dan coba lagi.',
                ])->toResponse($request)->setStatusCode(419);
            }

            return back()->withErrors([
                'error' => 'Sesi Anda telah berakhir. Silakan refresh halaman dan coba lagi.'
            ])->withInput();
        });

        // Handle 422 Validation Exception
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                ], 422);
            }

            if ($request->header('X-Inertia')) {
                // For Inertia, validation errors are handled automatically
                // But we can show a custom page if needed
                return back()->withErrors($e->errors())->withInput();
            }

            return back()->withErrors($e->errors())->withInput();
        });

        // Handle 500 Internal Server Error
        $exceptions->render(function (\Throwable $e, $request) {
            // Only show custom error page for non-validation, non-auth errors
            if ($e instanceof \Illuminate\Validation\ValidationException ||
                $e instanceof \Illuminate\Auth\AuthenticationException ||
                $e instanceof \Illuminate\Auth\Access\AuthorizationException ||
                $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException ||
                $e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException ||
                $e instanceof \Illuminate\Http\Exceptions\PostTooLargeException) {
                return null; // Let other handlers deal with it
            }

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => config('app.debug') ? $e->getMessage() : 'Server Error',
                    'error' => 'server_error'
                ], 500);
            }

            if ($request->header('X-Inertia')) {
                return \Inertia\Inertia::render('Errors/500', [
                    'message' => config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan pada server. Tim kami telah diberitahu dan sedang memperbaikinya.',
                ])->toResponse($request)->setStatusCode(500);
            }

            return null; // Let Laravel handle it with default error page
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
