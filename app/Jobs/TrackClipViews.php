<?php

namespace App\Jobs;

use App\Models\Clip;
use App\Services\ViewValidationService;
use App\Services\PlatformApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TrackClipViews implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Clip $clip
    ) {}

    public function handle(
        ViewValidationService $viewValidationService,
        PlatformApiService $platformApiService
    ): void {
        try {
            // Fetch views from platform API using PlatformApiService
            $views = $platformApiService->fetchViews($this->clip);

            if ($views !== null) {
                $viewValidationService->trackViews($this->clip, $views);
            } else {
                Log::warning('Failed to fetch views from platform API', [
                    'clip_id' => $this->clip->id,
                    'platform' => $this->clip->platform,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('TrackClipViews failed: ' . $e->getMessage(), [
                'clip_id' => $this->clip->id,
                'exception' => $e,
            ]);
            throw $e;
        }
    }
}
