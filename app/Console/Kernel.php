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
