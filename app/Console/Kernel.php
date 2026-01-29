<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('conversations:keys:rotate')->daily();
        $schedule->command('backup:cleanup')->dailyAt('02:00')->timezone('UTC')->onFailure(function () {
            \Log::warning('backup:cleanup failed');
        })->onSuccess(function () {
            \Log::info('backup:cleanup succeeded');
        });
        $schedule->command('health:latency-monitor')->everyMinute();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
