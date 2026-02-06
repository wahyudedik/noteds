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

// Explorer Articles Sync
Schedule::command('articles:sync --source=rss')->dailyAt('02:00');
Schedule::command('articles:sync --source=reddit')->everySixHours();

// Post Trending Calculation
Schedule::command('posts:calculate-trending')->hourly();

// Post Analytics Aggregation (daily at 1 AM)
Schedule::command('posts:aggregate-analytics')->dailyAt('01:00');

// Publish Scheduled Posts (every minute)
Schedule::command('posts:publish-scheduled')->everyMinute();
// Notify before publish (every minute, 30 minutes ahead)
Schedule::job(new \App\Jobs\NotifyBeforePublish(30))->everyMinute();

// Stories cleanup (remove expired stories)
Schedule::command('stories:cleanup-expired')->everyFiveMinutes();

// Events reminders (send every 5 minutes)
Schedule::command('events:send-reminders')->everyFiveMinutes();
