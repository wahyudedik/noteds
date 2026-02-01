<?php

namespace App\Providers;

use App\Listeners\BroadcastUserNotification;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        Gate::policy(\App\Models\AnalyticsEvent::class, \App\Policies\AnalyticsEventPolicy::class);
        \App\Models\StockPrice::observe(\App\Observers\StockPriceObserver::class);

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
        Event::listen(NotificationSent::class, function (NotificationSent $event) {
            try {
                $type = method_exists($event->notification, 'toArray')
                    ? ($event->notification->toArray($event->notifiable)['type'] ?? get_class($event->notification))
                    : get_class($event->notification);
                Log::info('Notification sent', [
                    'channel' => $event->channel,
                    'type' => $type,
                    'notifiable_id' => method_exists($event->notifiable, 'getKey') ? $event->notifiable->getKey() : null,
                ]);
            } catch (\Throwable $e) {
                // ignore logging failures
            }
        });

        Event::listen(Login::class, function (Login $event) {
            app(\App\Services\GamificationService::class)->awardDailyLogin($event->user);
        });

        RateLimiter::for('analytics', function ($request) {
            $user = $request->user();
            $role = $user?->role ?? 'free';
            $base = config('ratelimit.analytics.per_minute');
            $overrides = config("ratelimit.role_overrides.$role.analytics.per_minute");
            $dyn = \Illuminate\Support\Facades\Cache::get('rate_limit:analytics');
            $limit = ($dyn['limit'] ?? null) ?: ($overrides ?: $base);
            return Limit::perMinute($limit)->by($user?->id ?: $request->ip());
        });

        RateLimiter::for('chat', function ($request) {
            $user = $request->user();
            $role = $user?->role ?? 'free';
            $base = config('ratelimit.chat.per_minute');
            $overrides = config("ratelimit.role_overrides.$role.chat.per_minute");
            $dyn = \Illuminate\Support\Facades\Cache::get('rate_limit:chat');
            $limit = ($dyn['limit'] ?? null) ?: ($overrides ?: $base);
            return Limit::perMinute($limit)->by($user?->id ?: $request->ip());
        });

        RateLimiter::for('search', function ($request) {
            $user = $request->user();
            $role = $user?->role ?? 'free';
            $base = config('ratelimit.search.per_minute');
            $overrides = config("ratelimit.role_overrides.$role.search.per_minute");
            $dyn = \Illuminate\Support\Facades\Cache::get('rate_limit:search');
            $limit = ($dyn['limit'] ?? null) ?: ($overrides ?: $base);
            return Limit::perMinute($limit)->by($user?->id ?: $request->ip());
        });
    }
}
