<?php

namespace App\Providers;

use App\Listeners\BroadcastUserNotification;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
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

        // Register domain event listeners
        Event::listen(
            \App\Events\PostReposted::class,
            \App\Listeners\TrackRepostAnalyticsListener::class
        );

        Event::listen(
            \App\Events\StockLowAlert::class,
            \App\Listeners\StockLowAlertListener::class
        );

        Event::listen(
            \App\Events\PricingRuleApplied::class,
            \App\Listeners\PricingRuleAppliedListener::class
        );

        Event::listen(
            \App\Events\SellerVerified::class,
            \App\Listeners\SellerVerifiedListener::class
        );

        Event::listen(
            \App\Events\SellerRatingUpdated::class,
            \App\Listeners\SellerRatingUpdatedListener::class
        );

        // Respect basic notification preferences when sending
        Event::listen(NotificationSending::class, function (NotificationSending $event) {
            $notifiable = $event->notifiable;

            if (! $notifiable instanceof \App\Models\User) {
                return null;
            }

            $settings = $notifiable->settings;
            $preferences = $settings?->notification_preferences ?? [];

            // Global email on/off
            if ($event->channel === 'mail' && array_key_exists('email_notifications', $preferences)) {
                if (! $preferences['email_notifications']) {
                    return false;
                }
            }

            // Global in-app (database) on/off
            if ($event->channel === 'database' && array_key_exists('in_app_notifications', $preferences)) {
                if (! $preferences['in_app_notifications']) {
                    return false;
                }
            }

            return null;
        });

        // Broadcast database notifications in real-time for in-app updates
        Event::listen(NotificationSent::class, BroadcastUserNotification::class);

        Event::listen(Login::class, function (Login $event) {
            app(\App\Services\GamificationService::class)->awardDailyLogin($event->user);
        });
    }
}
