<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class ClipperCacheService
{
    /**
     * Cache keys prefix.
     */
    private const CACHE_PREFIX = 'clipper:';

    /**
     * Get campaign statistics (cached).
     */
    public function getCampaignStats(string $campaignId, callable $callback, int $ttl = 300): array
    {
        $key = self::CACHE_PREFIX . "campaign:stats:{$campaignId}";
        
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Invalidate campaign stats cache.
     */
    public function invalidateCampaignStats(string $campaignId): void
    {
        $key = self::CACHE_PREFIX . "campaign:stats:{$campaignId}";
        Cache::forget($key);
    }

    /**
     * Get brand dashboard stats (cached).
     */
    public function getBrandDashboard(string $userId, callable $callback, int $ttl = 600): array
    {
        $key = self::CACHE_PREFIX . "brand:dashboard:{$userId}";
        
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Invalidate brand dashboard cache.
     */
    public function invalidateBrandDashboard(string $userId): void
    {
        $key = self::CACHE_PREFIX . "brand:dashboard:{$userId}";
        Cache::forget($key);
    }

    /**
     * Get clip views (cached).
     */
    public function getClipViews(string $clipId, callable $callback, int $ttl = 3600): int
    {
        $key = self::CACHE_PREFIX . "clip:views:{$clipId}";
        
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Invalidate clip views cache.
     */
    public function invalidateClipViews(string $clipId): void
    {
        $key = self::CACHE_PREFIX . "clip:views:{$clipId}";
        Cache::forget($key);
    }

    /**
     * Get available campaigns list (cached).
     */
    public function getAvailableCampaigns(callable $callback, int $ttl = 300): array
    {
        $key = self::CACHE_PREFIX . "campaign:available";
        
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Invalidate available campaigns cache.
     */
    public function invalidateAvailableCampaigns(): void
    {
        $key = self::CACHE_PREFIX . "campaign:available";
        Cache::forget($key);
    }

    /**
     * Get clipper profile (cached).
     */
    public function getClipperProfile(string $userId, callable $callback, int $ttl = 1800): array
    {
        $key = self::CACHE_PREFIX . "clipper:profile:{$userId}";
        
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Invalidate clipper profile cache.
     */
    public function invalidateClipperProfile(string $userId): void
    {
        $key = self::CACHE_PREFIX . "clipper:profile:{$userId}";
        Cache::forget($key);
    }

    /**
     * Clear all clipper-related cache.
     */
    public function clearAll(): void
    {
        // Use Redis to clear by pattern if available
        if (config('cache.default') === 'redis') {
            $pattern = self::CACHE_PREFIX . '*';
            $keys = Redis::keys($pattern);
            
            if (!empty($keys)) {
                Redis::del($keys);
            }
        } else {
            // Fallback: clear specific known keys
            // This is less efficient but works with any cache driver
            Cache::flush(); // Note: This clears ALL cache, use with caution
        }
    }

    /**
     * Clear cache for a specific campaign and related data.
     */
    public function clearCampaignCache(string $campaignId, ?string $creatorId = null): void
    {
        $this->invalidateCampaignStats($campaignId);
        $this->invalidateAvailableCampaigns();
        
        if ($creatorId) {
            $this->invalidateBrandDashboard($creatorId);
        }
    }

    /**
     * Clear cache for a specific clip and related data.
     */
    public function clearClipCache(string $clipId, ?string $campaignId = null, ?string $clipperId = null): void
    {
        $this->invalidateClipViews($clipId);
        
        if ($campaignId) {
            $this->invalidateCampaignStats($campaignId);
        }
        
        if ($clipperId) {
            $this->invalidateClipperProfile($clipperId);
        }
    }
}

