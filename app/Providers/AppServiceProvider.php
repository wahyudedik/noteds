<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register CurrencyService as singleton
        $this->app->singleton('CurrencyService', function ($app) {
            return new \App\Services\CurrencyService();
        });

        // Also bind by class name for type hinting
        $this->app->singleton(\App\Services\CurrencyService::class, function ($app) {
            return new \App\Services\CurrencyService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register schedule event listener to track scheduler runs
        if (class_exists(\Illuminate\Console\Events\ScheduledTaskStarting::class)) {
            \Illuminate\Support\Facades\Event::listen(
                \Illuminate\Console\Events\ScheduledTaskStarting::class,
                \App\Listeners\ScheduleEventListener::class
            );
        }

        // Force HTTP for Vite dev server URL in development
        // This prevents Laravel from using HTTPS when app is served over HTTPS
        if (config('app.env') === 'local' && !app()->runningInConsole()) {
            try {
                $request = request();
                if ($request) {
                    $host = $request->getHost();
                    $viteDevServerUrl = "http://{$host}:5173";

                    // Set environment variable for Laravel Vite plugin
                    if (!env('VITE_DEV_SERVER_URL')) {
                        // Use global putenv() function explicitly
                        if (function_exists('putenv')) {
                            \putenv("VITE_DEV_SERVER_URL={$viteDevServerUrl}");
                        }
                        $_ENV['VITE_DEV_SERVER_URL'] = $viteDevServerUrl;
                    }

                    // Use View Composer to fix Vite URLs in rendered views
                    \Illuminate\Support\Facades\View::composer('*', function ($view) use ($host) {
                        $view->with('_vite_host', $host);
                    });
                }
            } catch (\Exception $e) {
                // Silently fail if request is not available yet
                // The fix in public/index.php will handle it
            }
        }
    }
}
