<?php

namespace App\Jobs;

use App\Models\Clip;
use App\Services\ViewValidationService;
use App\Services\RewardCalculationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ValidateClipViews implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Clip $clip
    ) {}

    public function handle(
        ViewValidationService $viewValidationService,
        RewardCalculationService $rewardCalculationService
    ): void {
        try {
            // Check if enough time has passed (24-72 hours delay)
            $submittedAt = $this->clip->submitted_at;
            $delayHours = config('clipper.view_validation_delay_hours', 24);
            
            if (now()->diffInHours($submittedAt) < $delayHours) {
                // Not enough time has passed, reschedule
                $this->release(now()->addHours($delayHours - now()->diffInHours($submittedAt)));
                return;
            }

            // Validate views
            if ($viewValidationService->validateViews($this->clip)) {
                // Check for fraud
                if (!$viewValidationService->detectFraud($this->clip)) {
                    // Calculate reward
                    $reward = $rewardCalculationService->calculateReward(
                        $this->clip,
                        $this->clip->valid_views
                    );

                    $this->clip->pending_reward = $reward;
                    $this->clip->save();

                    // Auto approve if valid
                    $this->clip->approve();
                } else {
                    // Fraud detected, mark for manual review
                    Log::warning('Fraud detected for clip', ['clip_id' => $this->clip->id]);
                }
            }
        } catch (\Exception $e) {
            Log::error('ValidateClipViews failed: ' . $e->getMessage(), [
                'clip_id' => $this->clip->id,
                'exception' => $e,
            ]);
            throw $e;
        }
    }
}
