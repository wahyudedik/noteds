<?php

namespace App\Listeners;

use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Support\Facades\Cache;

class ScheduleEventListener
{
    /**
     * Handle the event.
     */
    public function handle(ScheduledTaskStarting $event): void
    {
        // Mark scheduler as running
        Cache::put('scheduler_last_run', now()->toDateTimeString(), now()->addHours(2));
    }
}

