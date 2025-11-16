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
        //
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
    }
}
