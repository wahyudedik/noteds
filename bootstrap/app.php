<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $webMiddleware = [
            \App\Http\Middleware\EnsureUserIsActive::class,
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\SanitizeInput::class,
        ];

        $middleware->web(append: $webMiddleware);

        // Fix Vite URLs to use HTTP in development when app is served over HTTPS
        // This prevents SSL errors when Vite dev server is on HTTP
        // Add at the end to process final HTML output
        if (env('APP_ENV') === 'local') {
            $middleware->web(append: [
                \App\Http\Middleware\FixViteUrls::class,
            ]);
        }

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'premium' => \App\Http\Middleware\EnsureUserHasPremium::class, // Kept for backward compatibility, but logic changed
            'kyc' => \App\Http\Middleware\EnsureKycComplete::class,
            'username.setup' => \App\Http\Middleware\EnsureUsernameSetup::class,
            'buyer' => \App\Http\Middleware\EnsureBuyerRole::class,
            'seller' => \App\Http\Middleware\EnsureSellerRole::class,
            'workspace.user' => \App\Http\Middleware\EnsureWorkspaceUser::class,
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'rate.limit' => \App\Http\Middleware\RateLimitSensitive::class,
            'not_admin_affiliate' => \App\Http\Middleware\EnsureNotAdminAffiliate::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\ContentSecurityPolicy::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // AI exception handlers removed - AI features have been removed
        // Ensure JSON responses for background upload routes
        $exceptions->render(function (\Exception $e, $request) {
            if ($request->is('notes/upload-background') || $request->routeIs('notes.upload-background')) {
                Log::error('Unhandled exception in upload background route', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'path' => $request->path(),
                    'user_id' => auth()->id(),
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Upload gagal. File akan otomatis diupload saat form disubmit.',
                    'can_retry' => true,
                ], 500);
            }
        });
    })->create();
