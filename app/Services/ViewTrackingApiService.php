<?php

namespace App\Services;

use App\Models\Clip;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class ViewTrackingApiService
{
    /**
     * Rate limit keys per platform.
     */
    private const RATE_LIMIT_KEYS = [
        'tiktok' => 'api_tiktok',
        'instagram' => 'api_instagram',
        'youtube' => 'api_youtube',
    ];

    /**
     * Rate limits per platform (requests per time period).
     */
    private const RATE_LIMITS = [
        'tiktok' => ['max' => 10, 'decay' => 60], // 10 requests per minute
        'instagram' => ['max' => 5, 'decay' => 60], // 5 requests per minute
        'youtube' => ['max' => 100, 'decay' => 86400], // 100 requests per day
    ];

    /**
     * Track views for a clip based on platform.
     */
    public function trackViews(Clip $clip): int
    {
        $platform = $clip->platform;
        $contentId = $clip->platform_content_id ?? $this->extractContentId($clip->content_url, $platform);

        if (!$contentId) {
            Log::warning('View tracking failed: No content ID', [
                'clip_id' => $clip->id,
                'platform' => $platform,
            ]);
            return 0;
        }

        // Check rate limit
        if (!$this->checkRateLimit($platform)) {
            Log::warning('View tracking rate limit exceeded', [
                'clip_id' => $clip->id,
                'platform' => $platform,
            ]);
            return $this->getFallbackViews($clip);
        }

        try {
            $views = match ($platform) {
                'tiktok' => $this->trackTikTokViews($contentId),
                'instagram' => $this->trackInstagramViews($contentId),
                'youtube' => $this->trackYouTubeViews($contentId),
                default => $this->getFallbackViews($clip),
            };

            // Cache the views
            $this->cacheViews($clip->id, $views);

            return $views;
        } catch (\Exception $e) {
            $this->handleApiError($platform, $e);
            return $this->getFallbackViews($clip);
        }
    }

    /**
     * Track TikTok views.
     */
    protected function trackTikTokViews(string $contentId): int
    {
        // TODO: Implement TikTok API integration
        // For now, return cached or fallback value
        // Option 1: Use TikTok Official API (if available)
        // Option 2: Use web scraping (with proper rate limiting)
        
        Log::info('TikTok view tracking not yet implemented', ['content_id' => $contentId]);
        
        // Placeholder: In production, implement actual API call
        throw new \Exception('TikTok API not implemented');
    }

    /**
     * Track Instagram views.
     */
    protected function trackInstagramViews(string $contentId): int
    {
        // TODO: Implement Instagram API integration
        // Option 1: Instagram Basic Display API / Graph API
        // Option 2: Web scraping (with strict rate limiting)
        
        Log::info('Instagram view tracking not yet implemented', ['content_id' => $contentId]);
        
        // Placeholder: In production, implement actual API call
        throw new \Exception('Instagram API not implemented');
    }

    /**
     * Track YouTube views.
     */
    protected function trackYouTubeViews(string $contentId): int
    {
        // TODO: Implement YouTube Data API v3
        // Use API key or OAuth 2.0
        // Endpoint: videos.list with statistics part
        
        $apiKey = config('services.youtube.api_key');
        
        if (!$apiKey) {
            Log::warning('YouTube API key not configured');
            throw new \Exception('YouTube API key not configured');
        }

        try {
            $response = Http::timeout(10)
                ->get('https://www.googleapis.com/youtube/v3/videos', [
                    'id' => $contentId,
                    'part' => 'statistics',
                    'key' => $apiKey,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['items'][0]['statistics']['viewCount'])) {
                    return (int) $data['items'][0]['statistics']['viewCount'];
                }
            }

            throw new \Exception('YouTube API returned invalid response');
        } catch (\Exception $e) {
            Log::error('YouTube API error', [
                'content_id' => $contentId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check rate limit for platform.
     */
    protected function checkRateLimit(string $platform): bool
    {
        if (!isset(self::RATE_LIMIT_KEYS[$platform]) || !isset(self::RATE_LIMITS[$platform])) {
            return true; // No rate limit for unknown platforms
        }

        $key = self::RATE_LIMIT_KEYS[$platform];
        $limit = self::RATE_LIMITS[$platform];

        if (RateLimiter::tooManyAttempts($key, $limit['max'])) {
            return false;
        }

        RateLimiter::hit($key, $limit['decay']);
        return true;
    }

    /**
     * Extract content ID from URL.
     */
    protected function extractContentId(string $url, string $platform): ?string
    {
        return match ($platform) {
            'youtube' => $this->extractYouTubeId($url),
            'tiktok' => $this->extractTikTokId($url),
            'instagram' => $this->extractInstagramId($url),
            default => null,
        };
    }

    /**
     * Extract YouTube video ID from URL.
     */
    protected function extractYouTubeId(string $url): ?string
    {
        // Support both youtube.com/watch?v= and youtu.be/ formats
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Extract TikTok video ID from URL.
     */
    protected function extractTikTokId(string $url): ?string
    {
        // Extract TikTok video ID from URL
        if (preg_match('/tiktok\.com\/.*\/video\/(\d+)/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Extract Instagram post ID from URL.
     */
    protected function extractInstagramId(string $url): ?string
    {
        // Extract Instagram post ID from URL
        if (preg_match('/instagram\.com\/(?:p|reel)\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Get fallback views (last known or cached).
     */
    protected function getFallbackViews(Clip $clip): int
    {
        // Try to get from cache first
        $cached = Cache::get("clip:views:{$clip->id}");
        if ($cached !== null) {
            return (int) $cached;
        }

        // Get last known views from tracking
        $lastTracking = $clip->viewTrackings()->latest('tracked_at')->first();
        if ($lastTracking) {
            return $lastTracking->views_count;
        }

        // Return current valid_views as fallback
        return $clip->valid_views;
    }

    /**
     * Cache views for a clip.
     */
    protected function cacheViews(string $clipId, int $views): void
    {
        Cache::put("clip:views:{$clipId}", $views, 3600); // 1 hour
    }

    /**
     * Handle API errors.
     */
    protected function handleApiError(string $platform, \Exception $e): void
    {
        Log::error("View tracking API error for {$platform}", [
            'platform' => $platform,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        // Notify admin if API is down for extended period
        // This could trigger an alert/notification
    }
}

