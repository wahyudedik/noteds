<?php

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
        $middleware->web(append: [
            \App\Http\Middleware\EnsureUserIsActive::class,
            \App\Http\Middleware\SetLocale::class,
        ]);
        
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'premium' => \App\Http\Middleware\EnsureUserHasPremium::class,
            'username.setup' => \App\Http\Middleware\EnsureUsernameSetup::class,
            'buyer' => \App\Http\Middleware\EnsureBuyerRole::class,
            'seller' => \App\Http\Middleware\EnsureSellerRole::class,
            'workspace.user' => \App\Http\Middleware\EnsureWorkspaceUser::class,
            'throttle.ai' => \App\Http\Middleware\ThrottleAiRequests::class,
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'ai.access' => \App\Http\Middleware\EnsureAiAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Helper function to check if request is for AI route or expects JSON
        $isAiRoute = function ($request) {
            $path = $request->path();
            $acceptHeader = $request->header('Accept', '');
            
            return $request->expectsJson() 
                || $request->wantsJson() 
                || $request->is('api/*') 
                || $request->is('ai/*') 
                || str_contains($path, '/ai/')
                || $request->routeIs('ai.*')
                || $request->routeIs('buyer-ai.*')
                || str_contains($acceptHeader, 'application/json')
                || $request->ajax();
        };

        // Ensure JSON responses for AI routes (routes that expect JSON)
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) use ($isAiRoute) {
            if ($isAiRoute($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) use ($isAiRoute) {
            if ($isAiRoute($request)) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'An error occurred',
                ], $e->getStatusCode());
            }
        });

        $exceptions->render(function (\Exception $e, $request) use ($isAiRoute) {
            // Only handle for AI routes to avoid breaking other routes
            if ($isAiRoute($request)) {
                \Log::error('Unhandled exception in AI route', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'path' => $request->path(),
                    'route' => $request->route()?->getName(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while processing your request. Please try again later.',
                ], 500);
            }
        });
        
        // Ensure JSON responses for background upload routes
        $exceptions->render(function (\Exception $e, $request) {
            if ($request->is('notes/upload-background') || $request->routeIs('notes.upload-background')) {
                \Log::error('Unhandled exception in upload background route', [
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
