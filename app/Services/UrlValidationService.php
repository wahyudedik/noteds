<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UrlValidationService
{
    /**
     * Allowed platforms and their URL patterns.
     */
    private const ALLOWED_PLATFORMS = [
        'tiktok' => [
            'patterns' => [
                'tiktok.com',
                'vm.tiktok.com',
                'www.tiktok.com',
            ],
        ],
        'instagram' => [
            'patterns' => [
                'instagram.com',
                'www.instagram.com',
            ],
        ],
        'youtube' => [
            'patterns' => [
                'youtube.com',
                'youtu.be',
                'www.youtube.com',
                'm.youtube.com',
            ],
        ],
        'other' => [
            'patterns' => [],
        ],
    ];

    /**
     * Blocked IP ranges (private, localhost, etc.).
     */
    private const BLOCKED_IP_RANGES = [
        '127.0.0.0/8',
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
        '169.254.0.0/16',
        '::1/128',
        'fc00::/7',
        'fe80::/10',
    ];

    /**
     * Validate content URL for clip submission.
     * Prevents SSRF attacks and validates platform URLs.
     */
    public function validateContentUrl(string $url, string $platform): array
    {
        $errors = [];

        // 1. Basic URL format validation
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $errors[] = 'Invalid URL format.';
            return ['valid' => false, 'errors' => $errors];
        }

        $parsedUrl = parse_url($url);
        if (!$parsedUrl || !isset($parsedUrl['host'])) {
            $errors[] = 'Invalid URL structure.';
            return ['valid' => false, 'errors' => $errors];
        }

        $host = strtolower($parsedUrl['host']);

        // 2. Validate platform-specific URL patterns
        if (!$this->validatePlatformUrl($host, $platform)) {
            $errors[] = "URL does not match the selected platform ({$platform}).";
        }

        // 3. SSRF Prevention - Check for private/localhost IPs
        if ($this->isBlockedHost($host)) {
            $errors[] = 'URL contains blocked host (localhost or private IP).';
        }

        // 4. Check for dangerous protocols
        $scheme = strtolower($parsedUrl['scheme'] ?? '');
        if (!in_array($scheme, ['http', 'https'])) {
            $errors[] = 'Only HTTP and HTTPS protocols are allowed.';
        }

        // 5. Resolve host to IP and check for private IPs (SSRF prevention)
        try {
            $ip = gethostbyname($host);
            if ($ip === $host) {
                // DNS resolution failed
                $errors[] = 'Unable to resolve hostname.';
            } elseif ($this->isPrivateIp($ip)) {
                $errors[] = 'URL resolves to a private IP address.';
            }
        } catch (\Exception $e) {
            Log::warning('URL validation DNS check failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            // Don't fail validation on DNS errors, but log it
        }

        // 6. Optional: Check URL accessibility (with timeout)
        // This can be resource-intensive, so make it optional
        if (config('clipper.validate_url_accessibility', false)) {
            if (!$this->checkUrlAccessibility($url)) {
                $errors[] = 'URL is not accessible.';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Validate URL matches platform pattern.
     */
    protected function validatePlatformUrl(string $host, string $platform): bool
    {
        if ($platform === 'other') {
            return true; // Allow any URL for 'other' platform
        }

        if (!isset(self::ALLOWED_PLATFORMS[$platform])) {
            return false;
        }

        $patterns = self::ALLOWED_PLATFORMS[$platform]['patterns'];
        
        foreach ($patterns as $pattern) {
            if (str_contains($host, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if host is blocked (localhost, etc.).
     */
    protected function isBlockedHost(string $host): bool
    {
        $blockedHosts = [
            'localhost',
            '127.0.0.1',
            '0.0.0.0',
            '::1',
        ];

        foreach ($blockedHosts as $blocked) {
            if (str_contains($host, $blocked)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if IP is private/local.
     */
    protected function isPrivateIp(string $ip): bool
    {
        // Check IPv4
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        }

        // Check IPv6 (simplified - full implementation would need IP range checking)
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // Check for localhost IPv6
            if ($ip === '::1' || str_starts_with($ip, 'fe80::') || str_starts_with($ip, 'fc00::')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if URL is accessible (with timeout).
     */
    protected function checkUrlAccessibility(string $url): bool
    {
        try {
            $response = Http::timeout(5)
                ->withOptions(['verify' => true])
                ->head($url);

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('URL accessibility check failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Sanitize URL (remove tracking parameters, etc.).
     */
    public function sanitizeUrl(string $url): string
    {
        $parsedUrl = parse_url($url);
        if (!$parsedUrl) {
            return $url;
        }

        // Remove common tracking parameters
        $trackingParams = ['utm_source', 'utm_medium', 'utm_campaign', 'fbclid', 'gclid'];
        
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $params);
            foreach ($trackingParams as $param) {
                unset($params[$param]);
            }
            $parsedUrl['query'] = http_build_query($params);
        }

        $scheme = $parsedUrl['scheme'] ?? 'https';
        $host = $parsedUrl['host'] ?? '';
        $path = $parsedUrl['path'] ?? '';
        $query = isset($parsedUrl['query']) && $parsedUrl['query'] ? '?' . $parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';

        return "{$scheme}://{$host}{$path}{$query}{$fragment}";
    }
}

