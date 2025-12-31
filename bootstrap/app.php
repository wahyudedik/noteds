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
    })->create();
