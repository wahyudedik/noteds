<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
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
        Vite::prefetch(concurrency: 3);

        // Register event listeners
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\PostReposted::class,
            \App\Listeners\TrackRepostAnalyticsListener::class
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\StockLowAlert::class,
            \App\Listeners\StockLowAlertListener::class
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\PricingRuleApplied::class,
            \App\Listeners\PricingRuleAppliedListener::class
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\SellerVerified::class,
            \App\Listeners\SellerVerifiedListener::class
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\SellerRatingUpdated::class,
            \App\Listeners\SellerRatingUpdatedListener::class
        );
    }
}
