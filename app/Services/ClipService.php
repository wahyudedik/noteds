<?php

namespace App\Services;

use App\Models\User;
use App\Models\Campaign;
use App\Models\Clip;
use App\Services\RewardCalculationService;
use App\Services\ViewValidationService;
use App\Services\ClipperCacheService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

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
        try {
            return DB::transaction(function () use ($clipper, $campaign, $data) {
                // Check if campaign exists
                if (!$campaign || !$campaign->exists) {
                    throw new Exception('Campaign not found. The campaign may have been deleted.');
                }

                // Check if campaign is expired
                if ($campaign->isExpired()) {
                    throw new Exception('This campaign has expired and is no longer accepting submissions.');
                }

                // Check if campaign is available
                if ($campaign->status !== 'active') {
                    $statusMessage = match($campaign->status) {
                        'draft' => 'This campaign is still in draft and not yet active.',
                        'paused' => 'This campaign is currently paused and not accepting submissions.',
                        'completed' => 'This campaign has been completed and is no longer accepting submissions.',
                        'cancelled' => 'This campaign has been cancelled.',
                        default => 'This campaign is not currently active.',
                    };
                    throw new Exception($statusMessage);
                }

                // Check if campaign has budget
                $remainingBudget = $campaign->getRemainingBudget();
                if ($remainingBudget <= 0) {
                    throw new Exception('This campaign has exhausted its budget and is no longer accepting submissions.');
                }

                // Check for duplicate clip submission (same clipper, same campaign)
                $existingClip = Clip::where('clipper_id', $clipper->id)
                    ->where('campaign_id', $campaign->id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->first();

                if ($existingClip) {
                    throw new Exception('You have already submitted a clip for this campaign. Please wait for approval or submit to a different campaign.');
                }

                // Validate required fields
                if (empty($data['content_url'])) {
                    throw new Exception('Content URL is required to submit a clip.');
                }

                if (empty($data['platform'])) {
                    throw new Exception('Platform is required to submit a clip.');
                }

                try {
                    $clip = Clip::create([
                        'campaign_id' => $campaign->id,
                        'clipper_id' => $clipper->id,
                        'content_url' => $data['content_url'],
                        'platform' => $data['platform'],
                        'platform_content_id' => $data['platform_content_id'] ?? null,
                        'status' => 'pending',
                        'submitted_at' => now(),
                    ]);
                } catch (Exception $e) {
                    Log::error('Failed to create clip', [
                        'clipper_id' => $clipper->id,
                        'campaign_id' => $campaign->id,
                        'error' => $e->getMessage(),
                    ]);
                    throw new Exception('Failed to submit clip. Please try again or contact support if the problem persists.');
                }

                // Increment campaign clips count
                try {
                    $campaign->increment('total_clips');
                } catch (Exception $e) {
                    Log::warning('Failed to increment campaign clips count', [
                        'campaign_id' => $campaign->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Don't fail the transaction for this
                }

                // Invalidate cache
                try {
                    $this->cacheService->clearCampaignCache($campaign->id, $campaign->creator_id);
                    $this->cacheService->invalidateAvailableCampaigns();
                } catch (Exception $e) {
                    Log::warning('Failed to invalidate cache after clip submission', [
                        'campaign_id' => $campaign->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Don't fail the transaction for cache issues
                }

                return $clip;
            });
        } catch (Exception $e) {
            Log::error('Clip submission failed', [
                'clipper_id' => $clipper->id,
                'campaign_id' => $campaign->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Approve clip (trigger auto transfer).
     */
    public function approveClip(Clip $clip, ?User $admin = null): bool
    {
        try {
            return DB::transaction(function () use ($clip, $admin) {
                // Validate clip exists and is in valid state
                if (!$clip || !$clip->exists) {
                    throw new Exception('Clip not found. The clip may have been deleted.');
                }

                if ($clip->status !== 'pending') {
                    throw new Exception("Cannot approve clip. Current status is '{$clip->status}'. Only pending clips can be approved.");
                }

                // Check if campaign still exists and is active
                if (!$clip->campaign || !$clip->campaign->exists) {
                    throw new Exception('Cannot approve clip. The associated campaign has been deleted.');
                }

                if ($clip->campaign->status !== 'active') {
                    throw new Exception("Cannot approve clip. The campaign is not active (status: {$clip->campaign->status}).");
                }

                // Check if campaign has sufficient budget
                $remainingBudget = $clip->campaign->getRemainingBudget();
                if ($remainingBudget <= 0) {
                    throw new Exception('Cannot approve clip. The campaign has no remaining budget.');
                }

                try {
                    // Calculate reward based on valid views
                    $validViews = $clip->valid_views ?? 0;
                    $reward = $this->rewardCalculationService->calculateReward($clip, $validViews);

                    // Update clip with reward
                    $clip->pending_reward = $reward;
                    $clip->approved_reward = $reward;
                } catch (Exception $e) {
                    Log::error('Failed to calculate reward for clip approval', [
                        'clip_id' => $clip->id,
                        'error' => $e->getMessage(),
                    ]);
                    throw new Exception('Failed to calculate reward. Please try again or contact support.');
                }

                // Approve clip
                if (!$clip->approve($admin)) {
                    throw new Exception('Failed to approve clip. The clip may have already been processed or is in an invalid state.');
                }

                // Create audit log
                if ($admin) {
                    try {
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
                    } catch (Exception $e) {
                        Log::warning('Failed to create audit log for clip approval', [
                            'clip_id' => $clip->id,
                            'admin_id' => $admin->id,
                            'error' => $e->getMessage(),
                        ]);
                        // Don't fail the transaction for audit log issues
                    }
                }

                // Notify clipper about approval
                try {
                    $notificationService = app(\App\Services\NotificationService::class);
                    $notificationService->notifyClipApproved($clip);
                } catch (Exception $e) {
                    Log::warning('Failed to send approval notification', [
                        'clip_id' => $clip->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Don't fail the transaction for notification issues
                }

                // Immediately trigger auto transfer
                try {
                    $autoTransferService = app(\App\Services\AutoTransferService::class);
                    $transferResult = $autoTransferService->transferRewardToClipper($clip);
                    
                    if (!$transferResult) {
                        // Transfer failed, log but don't fail approval
                        Log::warning('Auto transfer failed after clip approval', [
                            'clip_id' => $clip->id,
                            'clip_status' => $clip->status,
                            'approved_reward' => $clip->approved_reward,
                        ]);
                        // Clip tetap approved, transfer akan retry via scheduled job
                    }
                } catch (Exception $e) {
                    // Log error, but don't fail approval
                    Log::error('Auto transfer exception after clip approval', [
                        'clip_id' => $clip->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Clip tetap approved, transfer akan retry via scheduled job
                }

                // Invalidate cache
                try {
                    $this->cacheService->clearClipCache($clip->id, $clip->campaign_id, $clip->clipper_id);
                } catch (Exception $e) {
                    Log::warning('Failed to invalidate cache after clip approval', [
                        'clip_id' => $clip->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Don't fail the transaction for cache issues
                }

                return true;
            });
        } catch (Exception $e) {
            Log::error('Clip approval failed', [
                'clip_id' => $clip->id ?? null,
                'admin_id' => $admin->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Reject clip.
     */
    public function rejectClip(Clip $clip, string $reason, ?User $admin = null): bool
    {
        try {
            return DB::transaction(function () use ($clip, $reason, $admin) {
                // Validate clip exists
                if (!$clip || !$clip->exists) {
                    throw new Exception('Clip not found. The clip may have been deleted.');
                }

                if ($clip->status !== 'pending') {
                    throw new Exception("Cannot reject clip. Current status is '{$clip->status}'. Only pending clips can be rejected.");
                }

                // Validate rejection reason
                if (empty(trim($reason))) {
                    throw new Exception('Rejection reason is required.');
                }

                if (strlen($reason) > 1000) {
                    throw new Exception('Rejection reason must not exceed 1000 characters.');
                }

                // Reject clip
                if (!$clip->reject($reason, $admin)) {
                    throw new Exception('Failed to reject clip. The clip may have already been processed or is in an invalid state.');
                }

                // Create audit log
                if ($admin) {
                    try {
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
                    } catch (Exception $e) {
                        Log::warning('Failed to create audit log for clip rejection', [
                            'clip_id' => $clip->id,
                            'admin_id' => $admin->id,
                            'error' => $e->getMessage(),
                        ]);
                        // Don't fail the transaction for audit log issues
                    }
                }

                // Notify clipper about rejection
                try {
                    $clip->clipper->notify(new \App\Notifications\ClipRejectedNotification($clip));
                } catch (Exception $e) {
                    Log::warning('Failed to send rejection notification', [
                        'clip_id' => $clip->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Don't fail the transaction for notification issues
                }

                // Invalidate cache
                try {
                    $this->cacheService->clearClipCache($clip->id, $clip->campaign_id, $clip->clipper_id);
                } catch (Exception $e) {
                    Log::warning('Failed to invalidate cache after clip rejection', [
                        'clip_id' => $clip->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Don't fail the transaction for cache issues
                }

                return true;
            });
        } catch (Exception $e) {
            Log::error('Clip rejection failed', [
                'clip_id' => $clip->id ?? null,
                'admin_id' => $admin->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}

