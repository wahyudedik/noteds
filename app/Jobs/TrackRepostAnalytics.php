<?php

namespace App\Jobs;

use App\Models\Repost;
use App\Models\RepostAnalytics;
use App\Services\RepostAnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TrackRepostAnalytics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Repost $repost
    ) {}

    /**
     * Execute the job.
     */
    public function handle(RepostAnalyticsService $analyticsService): void
    {
        $analyticsService->trackRepost($this->repost);
    }
}
