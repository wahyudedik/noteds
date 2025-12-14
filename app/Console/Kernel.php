<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // ===== TRANSACTION CLEANUP =====
        // Auto-cleanup pending transactions every day at 3 AM
        // This clears pending top-up transactions older than 1 day
        $schedule->command('transactions:cleanup-pending --days=1 --verify')
            ->dailyAt('03:00')
            ->timezone('Asia/Jakarta')
            ->name('cleanup-pending-transactions-daily')
            ->withoutOverlapping()
            ->onSuccess(function () {
                \Illuminate\Support\Facades\Log::info('✅ Daily cleanup pending transactions completed');
            })
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::error('❌ Daily cleanup pending transactions failed');
            });

        // More aggressive cleanup weekly (3+ days old) - Sundays at 2 AM
        $schedule->command('transactions:cleanup-pending --days=3 --verify --force')
            ->weeklyOn(0, '02:00')
            ->timezone('Asia/Jakarta')
            ->name('cleanup-pending-transactions-weekly')
            ->withoutOverlapping()
            ->onSuccess(function () {
                \Illuminate\Support\Facades\Log::info('✅ Weekly cleanup pending transactions (3+ days) completed');
            })
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::error('❌ Weekly cleanup pending transactions failed');
            });

        // ===== ESCROW CLEANUP =====
        // Release escrows that are past grace period (daily at 4 AM)
        $schedule->command('escrows:auto-release')
            ->dailyAt('04:00')
            ->timezone('Asia/Jakarta')
            ->name('auto-release-escrows')
            ->withoutOverlapping()
            ->onSuccess(function () {
                \Illuminate\Support\Facades\Log::info('✅ Auto-release escrows completed');
            });

        // ===== SUBSCRIPTION RENEWALS =====
        // Renew expired subscriptions (daily at 5 AM)
        $schedule->command('subscriptions:renew')
            ->dailyAt('05:00')
            ->timezone('Asia/Jakarta')
            ->name('renew-subscriptions')
            ->withoutOverlapping()
            ->onSuccess(function () {
                \Illuminate\Support\Facades\Log::info('✅ Subscription renewals completed');
            });

        // ===== MIDTRANS PAYMENT SYNC =====
        // Sync pending topups with Midtrans every 5 minutes to catch missed webhooks
        $schedule->command('midtrans:sync-status --all')
            ->everyFiveMinutes()
            ->name('sync-midtrans-payment-status')
            ->withoutOverlapping()
            ->onSuccess(function () {
                \Illuminate\Support\Facades\Log::info('✅ Midtrans payment status sync completed');
            })
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::error('❌ Midtrans payment status sync failed');
            });

        // ===== MONITORING & REPORTING =====
        // Check for suspicious pending transactions (every 6 hours)
        $schedule->command('transactions:report-pending')
            ->everyFourHours()
            ->name('report-pending-transactions')
            ->withoutOverlapping();

        // Generate cleanup summary (daily at 6 AM)
        $schedule->command('transactions:cleanup-summary')
            ->dailyAt('06:00')
            ->timezone('Asia/Jakarta')
            ->name('cleanup-summary')
            ->withoutOverlapping();

        // ===== GROWTH HACKING & ENGAGEMENT =====
        // Process user streak rewards (daily at 1 AM)
        $schedule->command('growth:process --type=streaks')
            ->dailyAt('01:00')
            ->timezone('Asia/Jakarta')
            ->name('process-user-streaks')
            ->withoutOverlapping()
            ->onSuccess(function () {
                \Illuminate\Support\Facades\Log::info('✅ User streak rewards processed');
            });

        // Send engagement nudges (daily at 9 AM)
        $schedule->command('growth:process --type=nudges')
            ->dailyAt('09:00')
            ->timezone('Asia/Jakarta')
            ->name('send-engagement-nudges')
            ->withoutOverlapping()
            ->onSuccess(function () {
                \Illuminate\Support\Facades\Log::info('✅ Engagement nudges sent');
            });

        // Process creator quality bonuses (weekly on Monday at 8 AM)
        $schedule->command('growth:process --type=bonuses')
            ->weeklyOn(1, '08:00')
            ->timezone('Asia/Jakarta')
            ->name('process-quality-bonuses')
            ->withoutOverlapping()
            ->onSuccess(function () {
                \Illuminate\Support\Facades\Log::info('✅ Creator quality bonuses processed');
            });

        // ===== RECOMMENDATIONS & CONTENT =====
        // Refresh recommendation cache (every 6 hours)
        $schedule->command('recommendations:refresh')
            ->everySixHours()
            ->name('refresh-recommendations-cache')
            ->withoutOverlapping()
            ->onSuccess(function () {
                \Illuminate\Support\Facades\Log::info('✅ Recommendations cache refreshed');
            });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
