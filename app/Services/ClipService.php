<?php

namespace App\Services;

use App\Models\User;
use App\Models\Campaign;
use App\Models\Clip;
use App\Services\RewardCalculationService;
use App\Services\ViewValidationService;
use App\Services\ClipperCacheService;
use Illuminate\Support\Facades\DB;

class ClipService
{
    public function __construct(
        protected RewardCalculationService $rewardCalculationService,
        protected ViewValidationService $viewValidationService,
        protected ClipperCacheService $cacheService
    ) {}

    /**
     * Submit clip to campaign.
     */
    public function submitClip(User $clipper, Campaign $campaign, array $data): Clip
    {
        return DB::transaction(function () use ($clipper, $campaign, $data) {
            // Check if campaign is available
            if ($campaign->status !== 'active') {
                throw new \Exception('Campaign is not active');
            }

            // Check if campaign has budget
            if ($campaign->getRemainingBudget() <= 0) {
                throw new \Exception('Campaign has no remaining budget');
            }

            $clip = Clip::create([
                'campaign_id' => $campaign->id,
                'clipper_id' => $clipper->id,
                'content_url' => $data['content_url'],
                'platform' => $data['platform'],
                'platform_content_id' => $data['platform_content_id'] ?? null,
                'status' => 'pending',
                'submitted_at' => now(),
            ]);

            // Increment campaign clips count
            $campaign->increment('total_clips');

            // Invalidate cache
            $this->cacheService->clearCampaignCache($campaign->id, $campaign->creator_id);
            $this->cacheService->invalidateAvailableCampaigns();

            return $clip;
        });
    }

    /**
     * Approve clip (trigger auto transfer).
     */
    public function approveClip(Clip $clip, ?User $admin = null): bool
    {
        return DB::transaction(function () use ($clip, $admin) {
            // Calculate reward based on valid views
            $validViews = $clip->valid_views;
            $reward = $this->rewardCalculationService->calculateReward($clip, $validViews);

            // Update clip with reward
            $clip->pending_reward = $reward;
            $clip->approved_reward = $reward;

            // Approve clip
            if (!$clip->approve($admin)) {
                return false;
            }

            // Create audit log
            if ($admin) {
                \App\Models\AuditLog::logAction([
                    'admin_id' => $admin->id,
                    'action' => 'approve_clip',
                    'target_type' => 'clip',
                    'target_id' => $clip->id,
                    'new_value' => [
                        'status' => 'approved',
                        'reward' => $reward,
                    ],
                ]);
            }

            // Notify clipper about approval
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->notifyClipApproved($clip);

            // Invalidate cache
            $this->cacheService->clearClipCache($clip->id, $clip->campaign_id, $clip->clipper_id);

            return true;
        });
    }

    /**
     * Reject clip.
     */
    public function rejectClip(Clip $clip, string $reason, ?User $admin = null): bool
    {
        return DB::transaction(function () use ($clip, $reason, $admin) {
            // Reject clip
            if (!$clip->reject($reason, $admin)) {
                return false;
            }

            // Create audit log
            if ($admin) {
                \App\Models\AuditLog::logAction([
                    'admin_id' => $admin->id,
                    'action' => 'reject_clip',
                    'target_type' => 'clip',
                    'target_id' => $clip->id,
                    'new_value' => [
                        'status' => 'rejected',
                        'reason' => $reason,
                    ],
                ]);
            }

            // Notify clipper about rejection
            $clip->clipper->notify(new \App\Notifications\ClipRejectedNotification($clip));

            // Invalidate cache
            $this->cacheService->clearClipCache($clip->id, $clip->campaign_id, $clip->clipper_id);

            return true;
        });
    }
}

