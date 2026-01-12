<?php

namespace App\Services;

use App\Models\User;
use App\Models\Campaign;
use App\Models\CampaignTemplate;

class CampaignTemplateService
{
    /**
     * Create a template from a campaign.
     */
    public function createTemplate(User $user, array $data): CampaignTemplate
    {
        return CampaignTemplate::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'title' => $data['title'],
            'description' => $data['description'],
            'video_references' => $data['video_references'] ?? [],
            'cpm' => $data['cpm'],
            'max_budget' => $data['max_budget'],
            'max_reward_per_clipper' => $data['max_reward_per_clipper'] ?? null,
            'duration_days' => $data['duration_days'],
            'is_public' => $data['is_public'] ?? false,
        ]);
    }

    /**
     * Create a campaign from a template.
     */
    public function createFromTemplate(User $user, CampaignTemplate $template, ?array $overrides = []): Campaign
    {
        $campaignData = [
            'creator_id' => $user->id,
            'template_id' => $template->id,
            'title' => $overrides['title'] ?? $template->title,
            'description' => $overrides['description'] ?? $template->description,
            'video_references' => $overrides['video_references'] ?? $template->video_references,
            'cpm' => $overrides['cpm'] ?? $template->cpm,
            'max_budget' => $overrides['max_budget'] ?? $template->max_budget,
            'max_reward_per_clipper' => $overrides['max_reward_per_clipper'] ?? $template->max_reward_per_clipper,
            'duration_days' => $overrides['duration_days'] ?? $template->duration_days,
            'status' => 'draft',
        ];

        $campaign = Campaign::create($campaignData);
        
        // Increment template usage count
        $template->incrementUsage();
        
        return $campaign;
    }

    /**
     * Duplicate a template.
     */
    public function duplicateTemplate(CampaignTemplate $template, User $user): CampaignTemplate
    {
        return CampaignTemplate::create([
            'user_id' => $user->id,
            'name' => $template->name . ' (Copy)',
            'title' => $template->title,
            'description' => $template->description,
            'video_references' => $template->video_references,
            'cpm' => $template->cpm,
            'max_budget' => $template->max_budget,
            'max_reward_per_clipper' => $template->max_reward_per_clipper,
            'duration_days' => $template->duration_days,
            'is_public' => false, // Duplicates are private by default
        ]);
    }

    /**
     * Get user's templates.
     */
    public function getUserTemplates(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return CampaignTemplate::where('user_id', $user->id)
            ->latest()
            ->get();
    }

    /**
     * Get public templates.
     */
    public function getPublicTemplates(): \Illuminate\Database\Eloquent\Collection
    {
        return CampaignTemplate::where('is_public', true)
            ->orderBy('usage_count', 'desc')
            ->latest()
            ->get();
    }
}

