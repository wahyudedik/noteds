<?php

namespace App\Listeners;

use App\Events\PostReposted;
use App\Jobs\TrackRepostAnalytics;

class TrackRepostAnalyticsListener
{
    /**
     * Handle the event.
     */
    public function handle(PostReposted $event): void
    {
        // Dispatch job to track analytics
        TrackRepostAnalytics::dispatch($event->repost);
    }
}

