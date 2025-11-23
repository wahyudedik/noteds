<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AnalyticsTrackingService
{
    /**
     * Detect traffic source from referrer and UTM parameters.
     */
    public function detectTrafficSource(Request $request): string
    {
        $referrer = $request->header('referer') ?? $request->input('referrer');
        
        // Check UTM source first
        if ($utmSource = $request->input('utm_source')) {
            return match(strtolower($utmSource)) {
                'google', 'bing', 'yahoo', 'duckduckgo' => 'search',
                'facebook', 'twitter', 'instagram', 'linkedin', 'tiktok', 'youtube' => 'social',
                default => 'referral',
            };
        }

        // No referrer means direct traffic
        if (!$referrer) {
            return 'direct';
        }

        $referrerHost = parse_url($referrer, PHP_URL_HOST);
        
        // Search engines
        $searchEngines = ['google', 'bing', 'yahoo', 'duckduckgo', 'baidu', 'yandex'];
        foreach ($searchEngines as $engine) {
            if (stripos($referrerHost, $engine) !== false) {
                return 'search';
            }
        }

        // Social media
        $socialMedia = ['facebook', 'twitter', 'instagram', 'linkedin', 'tiktok', 'youtube', 'pinterest', 'reddit'];
        foreach ($socialMedia as $social) {
            if (stripos($referrerHost, $social) !== false) {
                return 'social';
            }
        }

        // Internal referral (same domain)
        $currentHost = $request->getHost();
        if ($referrerHost === $currentHost || str_ends_with($referrerHost, '.' . $currentHost)) {
            return 'direct';
        }

        // External referral
        return 'referral';
    }

    /**
     * Get geographic data from IP address (simplified version).
     * In production, use a service like MaxMind GeoIP2 or ipapi.co
     */
    public function getGeographicData(string $ipAddress): array
    {
        // Skip private/local IPs
        if ($this->isPrivateIp($ipAddress)) {
            return [
                'country_code' => null,
                'country_name' => null,
                'city' => null,
                'region' => null,
            ];
        }

        // Simple implementation - in production use proper GeoIP service
        // For now, return null values. You can integrate with ipapi.co or MaxMind
        try {
            // Example using ipapi.co (free tier: 1000 requests/day)
            // $response = Http::get("https://ipapi.co/{$ipAddress}/json/");
            // if ($response->successful()) {
            //     return [
            //         'country_code' => $response->json('country_code'),
            //         'country_name' => $response->json('country_name'),
            //         'city' => $response->json('city'),
            //         'region' => $response->json('region'),
            //     ];
            // }
        } catch (\Exception $e) {
            // Fail silently
        }

        return [
            'country_code' => null,
            'country_name' => null,
            'city' => null,
            'region' => null,
        ];
    }

    /**
     * Check if IP is private/local.
     */
    private function isPrivateIp(string $ip): bool
    {
        return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * Extract UTM parameters from request.
     */
    public function getUtmParameters(Request $request): array
    {
        return [
            'utm_source' => $request->input('utm_source'),
            'utm_medium' => $request->input('utm_medium'),
            'utm_campaign' => $request->input('utm_campaign'),
        ];
    }

    /**
     * Get hour of day (0-23).
     */
    public function getHour(): int
    {
        return (int) now()->format('H');
    }
}






