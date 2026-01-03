<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LinkPreviewService
{
    /**
     * Generate link preview from URL.
     *
     * @param string $url
     * @return array|null
     */
    public function generatePreview(string $url): ?array
    {
        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        // Check cache first (24 hours)
        $cacheKey = 'link_preview_' . md5($url);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            // Special handling for YouTube
            if ($this->isYouTubeUrl($url)) {
                $preview = $this->generateYouTubePreview($url);
                if ($preview) {
                    Cache::put($cacheKey, $preview, now()->addHours(24));
                    return $preview;
                }
            }

            // Special handling for Facebook
            if ($this->isFacebookUrl($url)) {
                $preview = $this->generateFacebookPreview($url);
                if ($preview) {
                    Cache::put($cacheKey, $preview, now()->addHours(24));
                    return $preview;
                }
            }

            // Fetch URL with timeout and better headers
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'Connection' => 'keep-alive',
                    'Upgrade-Insecure-Requests' => '1',
                ])
                ->get($url);

            if (!$response->successful()) {
                Log::warning('Link preview HTTP request failed', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);
                // Return basic preview for failed requests
                return $this->generateBasicPreview($url);
            }

            $html = $response->body();
            
            // Check if HTML is valid (not empty, not error page)
            if (empty($html) || strlen($html) < 100) {
                Log::warning('Link preview returned empty or invalid HTML', [
                    'url' => $url,
                    'html_length' => strlen($html),
                ]);
                return $this->generateBasicPreview($url);
            }

            $preview = $this->parseHtml($html, $url);

            if ($preview) {
                Cache::put($cacheKey, $preview, now()->addHours(24));
            } else {
                // Fallback to basic preview if parsing fails
                $preview = $this->generateBasicPreview($url);
            }

            return $preview;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('Link preview connection failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return $this->generateBasicPreview($url);
        } catch (\Exception $e) {
            Log::warning('Link preview generation failed', [
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->generateBasicPreview($url);
        }
    }

    /**
     * Check if URL is a YouTube URL.
     */
    private function isYouTubeUrl(string $url): bool
    {
        return preg_match('/youtube\.com\/watch\?v=|youtu\.be\//', $url);
    }

    /**
     * Generate preview for YouTube URL.
     */
    private function generateYouTubePreview(string $url): ?array
    {
        // Extract video ID
        $videoId = null;
        if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $url, $matches)) {
            $videoId = $matches[1];
        } elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            $videoId = $matches[1];
        }

        if (!$videoId) {
            return null;
        }

        // Try to fetch video info from YouTube oEmbed API
        try {
            $response = Http::timeout(5)->get('https://www.youtube.com/oembed', [
                'url' => $url,
                'format' => 'json',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'url' => $url,
                    'title' => $data['title'] ?? 'YouTube Video',
                    'description' => $data['author_name'] ?? 'YouTube',
                    'image' => $data['thumbnail_url'] ?? "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg",
                    'site_name' => 'YouTube',
                ];
            }
        } catch (\Exception $e) {
            Log::warning('YouTube oEmbed API failed', ['url' => $url, 'error' => $e->getMessage()]);
        }

        // Fallback: use YouTube thumbnail
        return [
            'url' => $url,
            'title' => 'YouTube Video',
            'description' => 'Watch on YouTube',
            'image' => "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg",
            'site_name' => 'YouTube',
        ];
    }

    /**
     * Parse HTML to extract meta tags.
     */
    private function parseHtml(string $html, string $url): ?array
    {
        // Extract Open Graph tags and fallback to standard meta tags
        $title = $this->extractMetaTag($html, 'og:title') 
            ?: $this->extractTag($html, 'title')
            ?: parse_url($url, PHP_URL_HOST);

        $description = $this->extractMetaTag($html, 'og:description')
            ?: $this->extractMetaTag($html, 'description')
            ?: '';

        $image = $this->extractMetaTag($html, 'og:image');
        if ($image && !filter_var($image, FILTER_VALIDATE_URL)) {
            // Convert relative URL to absolute
            $image = $this->makeAbsoluteUrl($image, $url);
        }

        $siteName = $this->extractMetaTag($html, 'og:site_name')
            ?: parse_url($url, PHP_URL_HOST);

        return [
            'url' => $url,
            'title' => $this->cleanText($title),
            'description' => $this->cleanText($description),
            'image' => $image,
            'site_name' => $this->cleanText($siteName),
        ];
    }

    /**
     * Extract Open Graph or meta tag value.
     */
    private function extractMetaTag(string $html, string $property): ?string
    {
        // Try Open Graph first
        if (preg_match('/<meta\s+property=["\']og:' . preg_quote($property, '/') . '["\']\s+content=["\']([^"\']+)["\']/i', $html, $matches)) {
            return $matches[1];
        }

        // Try standard meta tag
        if (preg_match('/<meta\s+name=["\']' . preg_quote($property, '/') . '["\']\s+content=["\']([^"\']+)["\']/i', $html, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract content from HTML tag.
     */
    private function extractTag(string $html, string $tag): ?string
    {
        if (preg_match('/<' . preg_quote($tag, '/') . '[^>]*>([^<]+)<\/' . preg_quote($tag, '/') . '>/i', $html, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Convert relative URL to absolute URL.
     */
    private function makeAbsoluteUrl(string $url, string $baseUrl): string
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        $parsedBase = parse_url($baseUrl);
        $scheme = $parsedBase['scheme'] ?? 'https';
        $host = $parsedBase['host'] ?? '';

        if (str_starts_with($url, '//')) {
            return $scheme . ':' . $url;
        }

        if (str_starts_with($url, '/')) {
            return $scheme . '://' . $host . $url;
        }

        $path = $parsedBase['path'] ?? '/';
        $path = dirname($path);
        if ($path === '.') {
            $path = '/';
        }

        return $scheme . '://' . $host . rtrim($path, '/') . '/' . $url;
    }

    /**
     * Clean and truncate text.
     */
    private function cleanText(?string $text, int $maxLength = 200): string
    {
        if (!$text) {
            return '';
        }

        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = strip_tags($text);
        $text = trim($text);

        if (mb_strlen($text) > $maxLength) {
            $text = mb_substr($text, 0, $maxLength) . '...';
        }

        return $text;
    }

    /**
     * Check if URL is a Facebook URL.
     */
    private function isFacebookUrl(string $url): bool
    {
        return preg_match('/facebook\.com|fb\.com|fb\.me/', $url);
    }

    /**
     * Generate preview for Facebook URL.
     */
    private function generateFacebookPreview(string $url): ?array
    {
        // Facebook URLs are difficult to scrape, return basic preview
        $host = parse_url($url, PHP_URL_HOST);
        
        return [
            'url' => $url,
            'title' => 'Facebook Post',
            'description' => 'View this post on Facebook',
            'image' => null,
            'site_name' => 'Facebook',
        ];
    }

    /**
     * Generate basic preview when scraping fails.
     */
    private function generateBasicPreview(string $url): array
    {
        $host = parse_url($url, PHP_URL_HOST);
        $host = str_replace('www.', '', $host ?? '');
        
        return [
            'url' => $url,
            'title' => $host ? ucfirst($host) : 'Link',
            'description' => 'Click to view this link',
            'image' => null,
            'site_name' => $host ?: 'Website',
        ];
    }
}

