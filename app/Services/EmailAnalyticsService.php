<?php

namespace App\Services;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailAbTest;
use Illuminate\Support\Facades\DB;

class EmailAnalyticsService
{
    /**
     * Get campaign analytics
     */
    public function getCampaignAnalytics(EmailCampaign $campaign): array
    {
        $recipients = $campaign->recipients;
        
        $total = $recipients->count();
        $sent = $recipients->where('status', 'sent')->count();
        $failed = $recipients->where('status', 'failed')->count();
        $opened = $recipients->whereNotNull('opened_at')->count();
        $clicked = $recipients->whereNotNull('clicked_at')->count();
        
        return [
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'opened' => $opened,
            'clicked' => $clicked,
            'open_rate' => $sent > 0 ? round(($opened / $sent) * 100, 2) : 0,
            'click_rate' => $sent > 0 ? round(($clicked / $sent) * 100, 2) : 0,
            'click_to_open_rate' => $opened > 0 ? round(($clicked / $opened) * 100, 2) : 0,
        ];
    }

    /**
     * Get A/B test analytics
     */
    public function getAbTestAnalytics(EmailAbTest $abTest): array
    {
        $results = [];
        
        foreach ($abTest->variants as $variant) {
            $variantId = $variant['id'] ?? null;
            if (!$variantId) continue;
            
            $recipients = EmailCampaignRecipient::where('ab_test_id', $abTest->id)
                ->where('ab_variant_id', $variantId)
                ->get();
            
            $total = $recipients->count();
            $sent = $recipients->where('status', 'sent')->count();
            $opened = $recipients->whereNotNull('opened_at')->count();
            $clicked = $recipients->whereNotNull('clicked_at')->count();
            
            $results[$variantId] = [
                'variant' => $variant,
                'total' => $total,
                'sent' => $sent,
                'opened' => $opened,
                'clicked' => $clicked,
                'open_rate' => $sent > 0 ? round(($opened / $sent) * 100, 2) : 0,
                'click_rate' => $sent > 0 ? round(($clicked / $sent) * 100, 2) : 0,
                'click_to_open_rate' => $opened > 0 ? round(($clicked / $opened) * 100, 2) : 0,
            ];
        }
        
        return $results;
    }

    /**
     * Get overall email analytics
     */
    public function getOverallAnalytics(array $filters = []): array
    {
        $query = EmailCampaignRecipient::query();
        
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
        
        if (isset($filters['campaign_id'])) {
            $query->where('campaign_id', $filters['campaign_id']);
        }
        
        $recipients = $query->get();
        
        $total = $recipients->count();
        $sent = $recipients->where('status', 'sent')->count();
        $opened = $recipients->whereNotNull('opened_at')->count();
        $clicked = $recipients->whereNotNull('clicked_at')->count();
        
        return [
            'total' => $total,
            'sent' => $sent,
            'opened' => $opened,
            'clicked' => $clicked,
            'open_rate' => $sent > 0 ? round(($opened / $sent) * 100, 2) : 0,
            'click_rate' => $sent > 0 ? round(($clicked / $sent) * 100, 2) : 0,
            'click_to_open_rate' => $opened > 0 ? round(($clicked / $opened) * 100, 2) : 0,
        ];
    }

    /**
     * Get top performing campaigns
     */
    public function getTopCampaigns(int $limit = 10): array
    {
        return EmailCampaign::withCount(['recipients as sent_count' => function ($query) {
                $query->where('status', 'sent');
            }])
            ->withCount(['recipients as opened_count' => function ($query) {
                $query->whereNotNull('opened_at');
            }])
            ->withCount(['recipients as clicked_count' => function ($query) {
                $query->whereNotNull('clicked_at');
            }])
            ->orderByDesc('sent_count')
            ->limit($limit)
            ->get()
            ->map(function ($campaign) {
                $openRate = $campaign->sent_count > 0 
                    ? round(($campaign->opened_count / $campaign->sent_count) * 100, 2) 
                    : 0;
                $clickRate = $campaign->sent_count > 0 
                    ? round(($campaign->clicked_count / $campaign->sent_count) * 100, 2) 
                    : 0;
                
                return [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'type' => $campaign->type,
                    'sent' => $campaign->sent_count,
                    'opened' => $campaign->opened_count,
                    'clicked' => $campaign->clicked_count,
                    'open_rate' => $openRate,
                    'click_rate' => $clickRate,
                ];
            })
            ->toArray();
    }
}

