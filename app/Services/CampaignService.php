<?php

namespace App\Services;

use App\Models\User;
use App\Models\Campaign;
use App\Models\Post;
use App\Services\EscrowService;
use App\Services\ClipperCacheService;
use Illuminate\Support\Facades\DB;

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
        return DB::transaction(function () use ($creator, $data) {
            $campaign = Campaign::create([
                'creator_id' => $creator->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'cpm' => $data['cpm'],
                'max_budget' => $data['max_budget'],
                'max_reward_per_clipper' => $data['max_reward_per_clipper'] ?? null,
                'duration_days' => $data['duration_days'],
                'status' => 'draft',
            ]);

            return $campaign;
        });
    }

    /**
     * Activate campaign (lock budget).
     */
    public function activateCampaign(Campaign $campaign): bool
    {
        return DB::transaction(function () use ($campaign) {
            if (!$campaign->canActivate()) {
                return false;
            }

            // Lock budget
            if (!$this->escrowService->lockCampaignBudget($campaign)) {
                return false;
            }

            // Activate campaign
            $result = $campaign->activate();
            
            // Invalidate cache
            if ($result) {
                $this->cacheService->clearCampaignCache($campaign->id, $campaign->creator_id);
                $this->cacheService->invalidateAvailableCampaigns();
            }
            
            return $result;
        });
    }

    /**
     * Pause campaign.
     */
    public function pauseCampaign(Campaign $campaign): bool
    {
        return $campaign->pause();
    }

    /**
     * Complete campaign (refund remaining budget).
     */
    public function completeCampaign(Campaign $campaign): bool
    {
        return DB::transaction(function () use ($campaign) {
            // Refund remaining budget
            $this->escrowService->refundRemainingBudget($campaign);

            // Complete campaign
            return $campaign->complete();
        });
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

