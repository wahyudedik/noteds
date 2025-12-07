<?php

use App\Helpers\CurrencyHelper;
use App\Helpers\TimeHelper;

if (!function_exists('currency')) {
    /**
     * Format currency helper
     */
    function currency(float $amount = 0, ?string $currency = null, ?string $fromCurrency = null): string
    {
        return CurrencyHelper::format((float) $amount, $currency, $fromCurrency);
    }
}

if (!function_exists('localized_time')) {
    /**
     * Format time helper
     */
    function localized_time($datetime, string $format = 'default', ?string $timezone = null): string
    {
        return TimeHelper::format($datetime, $format, $timezone);
    }
}

if (!function_exists('localized_diff_for_humans')) {
    /**
     * Human-readable time difference helper
     */
    function localized_diff_for_humans($datetime, ?string $locale = null): string
    {
        return TimeHelper::diffForHumans($datetime, $locale);
    }
}

if (!function_exists('responsive_image')) {
    /**
     * Generate responsive image attributes (srcset and sizes)
     * For better performance and SEO
     */
    function responsive_image(string $imagePath, array $sizes = [300, 600, 900, 1200], string $defaultSize = '600'): array
    {
        $baseUrl = \Illuminate\Support\Facades\Storage::url($imagePath);
        $pathInfo = pathinfo($imagePath);
        $extension = $pathInfo['extension'] ?? 'jpg';
        $dirname = $pathInfo['dirname'] ?? '';
        $filename = $pathInfo['filename'] ?? '';
        
        // Generate srcset
        $srcset = [];
        foreach ($sizes as $size) {
            // In a real implementation, you'd generate different sized images
            // For now, we'll use the same image with size parameter (if your image service supports it)
            $srcset[] = $baseUrl . "?w={$size} {$size}w";
        }
        
        return [
            'src' => $baseUrl,
            'srcset' => implode(', ', $srcset),
            'sizes' => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw',
        ];
    }
}

if (!function_exists('cdn_url')) {
    /**
     * Get CDN URL for asset
     * Falls back to APP_URL if CDN_URL is not set
     */
    function cdn_url(string $path = ''): string
    {
        $baseUrl = env('CDN_URL', env('APP_URL'));
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset_cdn')) {
    /**
     * Generate asset URL with CDN support
     */
    function asset_cdn(string $path): string
    {
        if (env('CDN_URL')) {
            return cdn_url($path);
        }
        return asset($path);
    }
}

