<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule subscription renewal check (run daily at 00:00)
Schedule::command('subscriptions:renew')
    ->daily()
    ->at('00:00')
    ->timezone('Asia/Jakarta')
    ->description('Auto-renew subscriptions or expire if insufficient wallet balance');

// Schedule featured notes expiry check (run daily at 01:00)
Schedule::command('featured:expire')
    ->daily()
    ->at('01:00')
    ->timezone('Asia/Jakarta')
    ->description('Expire featured notes that have passed their end date');

// Schedule activate scheduled featured notes (run hourly)
Schedule::command('featured:activate-scheduled')
    ->hourly()
    ->timezone('Asia/Jakarta')
    ->description('Activate scheduled featured notes that have reached their scheduled date');

Schedule::command('forum:publish-scheduled-posts')
    ->everyMinute()
    ->timezone('Asia/Jakarta')
    ->description('Publish forum posts that have reached their scheduled time');

// Schedule publish scheduled notes (run every minute)
Schedule::command('notes:publish-scheduled')
    ->everyMinute()
    ->timezone('Asia/Jakarta')
    ->description('Publish notes that have reached their scheduled publishing time');
Schedule::command('workspace:digest')
    ->daily()
    ->at('07:00')
    ->timezone('Asia/Jakarta')
    ->description('Send daily workspace activity digest to members.');

Schedule::command('marketplace:daily-digest')
    ->daily()
    ->at('08:00')
    ->timezone('Asia/Jakarta')
    ->description('Send daily sales digest to sellers.');

Schedule::command('featured:expiry-reminders')
    ->daily()
    ->at('09:00')
    ->timezone('Asia/Jakarta')
    ->description('Send reminders for featured notes that are about to expire.');

// Studio SLA reminders (milestones due and funding reminders)
Schedule::command('studio:sla-reminders')
    ->hourly()
    ->timezone('Asia/Jakarta')
    ->description('Send SLA reminders for Studio milestones and funding.');

// Schedule view revenue validation (run every hour)
Schedule::command('views:validate --limit=200')
    ->hourly()
    ->timezone('Asia/Jakarta')
    ->description('Validate pending view revenues and mark as approved or rejected.');

// Schedule expired points processing (run daily at 02:00)
Schedule::command('points:process-expired')
    ->daily()
    ->at('02:00')
    ->timezone('Asia/Jakarta')
    ->description('Process expired points and update statistics.');

// Email Campaign Schedules
// Send abandoned cart emails (run every 2 hours)
Schedule::command('email:abandoned-cart')
    ->everyTwoHours()
    ->timezone('Asia/Jakarta')
    ->description('Send abandoned cart reminder emails to users who viewed notes but didn\'t purchase.');

// Send weekly digest (run every Monday at 09:00)
Schedule::command('email:weekly-digest')
    ->weeklyOn(1, '09:00')
    ->timezone('Asia/Jakarta')
    ->description('Send weekly digest emails with recommended notes to users.');

// Process email sequences (run every hour)
Schedule::command('email:process-sequences')
    ->hourly()
    ->timezone('Asia/Jakarta')
    ->description('Process and send automated email sequences based on user triggers.');

// Expire certifications (run daily at 00:00)
Schedule::command('certifications:expire')
    ->daily()
    ->at('00:00')
    ->timezone('Asia/Jakarta')
    ->description('Check and expire certifications that have passed their expiration date.');

// Auto-release escrows (run hourly)
Schedule::command('escrow:auto-release')
    ->hourly()
    ->timezone('Asia/Jakarta')
    ->description('Auto-release escrows that have passed their auto-release date.');

// Auto-renew note subscriptions (run daily at 00:00)
Schedule::command('subscriptions:auto-renew')
    ->daily()
    ->at('00:00')
    ->timezone('Asia/Jakarta')
    ->description('Auto-renew note subscriptions that are due for renewal.');

// Expire note subscriptions (run daily at 01:00)
Schedule::command('subscriptions:expire')
    ->daily()
    ->at('01:00')
    ->timezone('Asia/Jakarta')
    ->description('Expire note subscriptions that have passed their expiration date.');

// Generate workspace insights (run weekly on Monday at 09:00)
Schedule::command('workspaces:generate-insights')
    ->weeklyOn(1, '9:00')
    ->timezone('Asia/Jakarta')
    ->description('Generate weekly digests and detect anomalies for all workspaces.');

// Send workspace reminders (run hourly)
Schedule::command('workspaces:send-reminders')
    ->hourly()
    ->timezone('Asia/Jakarta')
    ->description('Send due reminders for workspace tasks and notes.');

// Send daily notification digests (run hourly to check user preferences)
Schedule::command('notifications:send-daily-digest')
    ->hourly()
    ->timezone('UTC')
    ->description('Send daily email digests to users who have enabled it.');

// Send weekly notification digests (run daily on Monday to check user preferences)
Schedule::command('notifications:send-weekly-digest')
    ->daily()
    ->at('09:00')
    ->timezone('UTC')
    ->description('Send weekly email digests to users who have enabled it.');

// Distribute leaderboard rewards (run on 5th of each month at 10:00)
Schedule::job(new \App\Jobs\DistributeLeaderboardRewardsJob())
    ->monthly()
    ->timezone('Asia/Jakarta')
    ->description('Distribute monthly leaderboard rewards to top performers');

// Process monthly share commissions
// Run on the configured day each month at 11:00
$payoutDay = \App\Models\Setting::getSetting('share_monthly_payout_day', 'marketplace', 1);
Schedule::job(new \App\Jobs\ProcessMonthlyShareCommissionJob())
    ->monthlyOn($payoutDay, '11:00')
    ->timezone('Asia/Jakarta')
    ->description('Transfer accumulated share commissions from admin wallet to seller wallets');
