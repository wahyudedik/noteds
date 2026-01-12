<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule MediaStack articles fetch 3x per day
Schedule::command('mediastack:fetch')->dailyAt('08:00');
Schedule::command('mediastack:fetch')->dailyAt('14:00');
Schedule::command('mediastack:fetch')->dailyAt('20:00');

// Clipper System Scheduled Commands
Schedule::command('clipper:track-views')->everySixHours();
Schedule::command('clipper:validate-pending-clips')->hourly();
Schedule::command('clipper:auto-transfer-rewards')->everyFifteenMinutes();
Schedule::command('clipper:complete-expired-campaigns')->daily();

// Explorer Articles Sync
Schedule::command('articles:sync --source=rss')->dailyAt('02:00');
Schedule::command('articles:sync --source=reddit')->everySixHours();

// Post Trending Calculation
Schedule::command('posts:calculate-trending')->hourly();

// Post Analytics Aggregation (daily at 1 AM)
Schedule::command('posts:aggregate-analytics')->dailyAt('01:00');

// Publish Scheduled Posts (every minute)
Schedule::command('posts:publish-scheduled')->everyMinute();

// Campaign Scheduling Commands
Schedule::command('campaigns:activate-scheduled')->everyMinute();
Schedule::command('campaigns:complete-scheduled')->everyMinute();

// Seller Tools Scheduled Commands
Schedule::command('pricing:process')->hourly();
Schedule::command('seller-metrics:recalculate')->daily();
Schedule::command('inventory:check-low-stock')->everySixHours();

// Stock data collection (every minute during market hours: 9:00-16:00 WIB)
Schedule::command('stocks:collect-intraday')->everyMinute()
    ->between('9:00', '16:00')
    ->timezone('Asia/Jakarta')
    ->weekdays();

// Daily price collection (after market close)
Schedule::command('stocks:collect-daily')->dailyAt('16:15')
    ->timezone('Asia/Jakarta')
    ->weekdays();

// Update technical indicators (after daily price collection)
Schedule::command('stocks:update-indicators --all')->dailyAt('16:30')
    ->timezone('Asia/Jakarta')
    ->weekdays();

// Generate predictions (after market close)
Schedule::command('ml:generate-predictions --all')->dailyAt('17:00')
    ->timezone('Asia/Jakarta')
    ->weekdays();

// Generate signals (after predictions)
Schedule::command('signals:generate --all')->dailyAt('17:30')
    ->timezone('Asia/Jakarta')
    ->weekdays();

// Check prediction accuracy (compare with actuals)
Schedule::command('ml:check-accuracy')->dailyAt('18:00')
    ->timezone('Asia/Jakarta')
    ->weekdays();

// Expire old signals
Schedule::command('signals:expire')->hourly();

// Select best models (weekly, Sunday)
Schedule::command('ml:select-best-models')->weeklyOn(0, '2:00')
    ->timezone('Asia/Jakarta');

// Retrain models (weekly, Sunday)
Schedule::command('ml:retrain-models --all')->weeklyOn(0, '3:00')
    ->timezone('Asia/Jakarta');
