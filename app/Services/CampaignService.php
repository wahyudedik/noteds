<?php

namespace App\Services;

use App\Models\User;
use App\Models\Campaign;
use App\Models\Post;
use App\Services\EscrowService;
use App\Services\ClipperCacheService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CampaignService
{
    public function __construct(
        protected EscrowService $escrowService,
        protected ClipperCacheService $cacheService
    ) {}

    /**
     * Create campaign with budget validation.
     */
    public function createCampaign(User $creator, array $data): Campaign
    {
        try {
            return DB::transaction(function () use ($creator, $data) {
                // Validate creator
                if (!$creator || !$creator->exists) {
                    throw new Exception('Creator not found. Please ensure you are logged in.');
                }

                // Validate required fields
                if (empty(trim($data['title'] ?? ''))) {
                    throw new Exception('Campaign title is required.');
                }

                if (empty(trim($data['description'] ?? ''))) {
                    throw new Exception('Campaign description is required.');
                }

                if (!isset($data['cpm']) || $data['cpm'] <= 0) {
                    throw new Exception('CPM (Cost Per Mille) must be greater than 0.');
                }

                if (!isset($data['max_budget']) || $data['max_budget'] <= 0) {
                    throw new Exception('Maximum budget must be greater than 0.');
                }

                if (!isset($data['duration_days']) || $data['duration_days'] <= 0) {
                    throw new Exception('Campaign duration must be greater than 0 days.');
                }

                try {
                    $campaign = Campaign::create([
                        'creator_id' => $creator->id,
                        'title' => $data['title'],
                        'description' => $data['description'],
                        'video_references' => $data['video_references'] ?? [],
                        'cpm' => $data['cpm'],
                        'max_budget' => $data['max_budget'],
                        'max_reward_per_clipper' => $data['max_reward_per_clipper'] ?? null,
                        'duration_days' => $data['duration_days'],
                        'status' => 'draft',
                    ]);
                } catch (Exception $e) {
                    Log::error('Failed to create campaign', [
                        'creator_id' => $creator->id,
                        'error' => $e->getMessage(),
                    ]);
                    throw new Exception('Failed to create campaign. Please try again or contact support if the problem persists.');
                }

                return $campaign;
            });
        } catch (Exception $e) {
            Log::error('Campaign creation failed', [
                'creator_id' => $creator->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Activate campaign (lock budget).
     */
    public function activateCampaign(Campaign $campaign): bool
    {
        try {
            return DB::transaction(function () use ($campaign) {
                // Validate campaign exists
                if (!$campaign || !$campaign->exists) {
                    throw new Exception('Campaign not found. The campaign may have been deleted.');
                }

                if (!$campaign->canActivate()) {
                    $statusMessage = match($campaign->status) {
                        'active' => 'This campaign is already active.',
                        'completed' => 'This campaign has been completed and cannot be activated again.',
                        'cancelled' => 'This campaign has been cancelled and cannot be activated.',
                        default => 'This campaign cannot be activated in its current state.',
                    };
                    throw new Exception($statusMessage);
                }

                // Check if campaign is expired
                if ($campaign->isExpired()) {
                    throw new Exception('Cannot activate an expired campaign.');
                }

                // Lock budget
                try {
                    if (!$this->escrowService->lockCampaignBudget($campaign)) {
                        throw new Exception('Failed to lock campaign budget. Please check your wallet balance and try again.');
                    }
                } catch (Exception $e) {
                    Log::error('Failed to lock campaign budget', [
                        'campaign_id' => $campaign->id,
                        'error' => $e->getMessage(),
                    ]);
                    throw new Exception('Insufficient balance to activate this campaign. Please top up your wallet first.');
                }

                // Activate campaign
                try {
                    $result = $campaign->activate();
                    if (!$result) {
                        throw new Exception('Failed to activate campaign. Please try again.');
                    }
                } catch (Exception $e) {
                    // Rollback budget lock if activation fails
                    try {
                        $this->escrowService->releaseCampaignBudget($campaign);
                    } catch (Exception $rollbackError) {
                        Log::error('Failed to rollback budget after activation failure', [
                            'campaign_id' => $campaign->id,
                            'error' => $rollbackError->getMessage(),
                        ]);
                    }
                    throw $e;
                }
                
                // Invalidate cache
                try {
                    $this->cacheService->clearCampaignCache($campaign->id, $campaign->creator_id);
                    $this->cacheService->invalidateAvailableCampaigns();
                } catch (Exception $e) {
                    Log::warning('Failed to invalidate cache after campaign activation', [
                        'campaign_id' => $campaign->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Don't fail the transaction for cache issues
                }
                
                return $result;
            });
        } catch (Exception $e) {
            Log::error('Campaign activation failed', [
                'campaign_id' => $campaign->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Pause campaign.
     */
    public function pauseCampaign(Campaign $campaign): bool
    {
        try {
            if (!$campaign || !$campaign->exists) {
                throw new Exception('Campaign not found. The campaign may have been deleted.');
            }

            if ($campaign->status !== 'active') {
                throw new Exception("Cannot pause campaign. Current status is '{$campaign->status}'. Only active campaigns can be paused.");
            }

            $result = $campaign->pause();
            
            if ($result) {
                try {
                    $this->cacheService->clearCampaignCache($campaign->id, $campaign->creator_id);
                    $this->cacheService->invalidateAvailableCampaigns();
                } catch (Exception $e) {
                    Log::warning('Failed to invalidate cache after campaign pause', [
                        'campaign_id' => $campaign->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
            return $result;
        } catch (Exception $e) {
            Log::error('Campaign pause failed', [
                'campaign_id' => $campaign->id ?? null,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Resume campaign.
     */
    public function resumeCampaign(Campaign $campaign): bool
    {
        try {
            if (!$campaign || !$campaign->exists) {
                throw new Exception('Campaign not found. The campaign may have been deleted.');
            }

            if ($campaign->status !== 'paused') {
                throw new Exception("Cannot resume campaign. Current status is '{$campaign->status}'. Only paused campaigns can be resumed.");
            }

            // Check if campaign is expired
            if ($campaign->isExpired()) {
                throw new Exception('Cannot resume an expired campaign.');
            }

            // Check if campaign has budget
            $remainingBudget = $campaign->getRemainingBudget();
            if ($remainingBudget <= 0) {
                throw new Exception('Cannot resume campaign. The campaign has no remaining budget.');
            }

            $result = $campaign->resume();
            
            if ($result) {
                try {
                    $this->cacheService->clearCampaignCache($campaign->id, $campaign->creator_id);
                    $this->cacheService->invalidateAvailableCampaigns();
                } catch (Exception $e) {
                    Log::warning('Failed to invalidate cache after campaign resume', [
                        'campaign_id' => $campaign->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
            return $result;
        } catch (Exception $e) {
            Log::error('Campaign resume failed', [
                'campaign_id' => $campaign->id ?? null,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Complete campaign (refund remaining budget).
     */
    public function completeCampaign(Campaign $campaign): bool
    {
        try {
            return DB::transaction(function () use ($campaign) {
                if (!$campaign || !$campaign->exists) {
                    throw new Exception('Campaign not found. The campaign may have been deleted.');
                }

                try {
                    // Refund remaining budget
                    $this->escrowService->refundRemainingBudget($campaign);
                } catch (Exception $e) {
                    Log::error('Failed to refund remaining budget', [
                        'campaign_id' => $campaign->id,
                        'error' => $e->getMessage(),
                    ]);
                    throw new Exception('Failed to refund remaining budget. Please contact support.');
                }

                // Complete campaign
                try {
                    $result = $campaign->complete();
                    if (!$result) {
                        throw new Exception('Failed to complete campaign. Please try again.');
                    }
                } catch (Exception $e) {
                    Log::error('Failed to complete campaign', [
                        'campaign_id' => $campaign->id,
                        'error' => $e->getMessage(),
                    ]);
                    throw new Exception('Failed to complete campaign. Please try again or contact support.');
                }

                return $result;
            });
        } catch (Exception $e) {
            Log::error('Campaign completion failed', [
                'campaign_id' => $campaign->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Share campaign as post in forum.
     */
    public function shareCampaignAsPost(Campaign $campaign, ?string $customMessage = null): Post
    {
        // Check if post already exists for this campaign
        if ($campaign->post) {
            return $campaign->post;
        }

        $campaignUrl = route('clipper.campaigns.show', $campaign);
        $message = $customMessage ?? "I've created a new campaign: {$campaign->title}. Check it out and submit your clips!";
        
        $content = "{$message}\n\n";
        $content .= "📊 Campaign Details:\n";
        $content .= "• CPM: Rp " . number_format($campaign->cpm, 0, ',', '.') . " per 1000 views\n";
        $content .= "• Budget: Rp " . number_format($campaign->max_budget, 0, ',', '.') . "\n";
        $content .= "• Duration: {$campaign->duration_days} days\n\n";
        $content .= "View campaign: {$campaignUrl}";

        $post = Post::create([
            'user_id' => $campaign->creator_id,
            'campaign_id' => $campaign->id,
            'purpose_type' => 'share_experience',
            'title' => "New Campaign: {$campaign->title}",
            'content' => $content,
            'status' => 'active',
        ]);

        return $post;
    }
}

