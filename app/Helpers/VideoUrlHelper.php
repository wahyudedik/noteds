<?php

namespace App\Helpers;

class VideoUrlHelper
{
    /**
     * Check if URL is a valid YouTube URL.
     */
    public static function isYouTubeUrl(string $url): bool
    {
        $patterns = [
            '/^https?:\/\/(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)/i',
            '/^https?:\/\/youtube\.com\/watch\?v=/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if URL is a valid Google Drive URL.
     */
    public static function isGoogleDriveUrl(string $url): bool
    {
        $patterns = [
            '/^https?:\/\/(drive|docs)\.google\.com\/(file\/d\/|open\?id=|file\/d\/)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract YouTube video ID from URL.
     */
    public static function extractYouTubeId(string $url): ?string
    {
        if (!self::isYouTubeUrl($url)) {
            return null;
        }

        // Pattern for youtube.com/watch?v=VIDEO_ID
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract Google Drive file ID from URL.
     */
    public static function extractGoogleDriveId(string $url): ?string
    {
        if (!self::isGoogleDriveUrl($url)) {
            return null;
        }

        // Pattern for drive.google.com/file/d/FILE_ID/view
        if (preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        // Pattern for drive.google.com/open?id=FILE_ID
        if (preg_match('/[?&]id=([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get video type from URL (youtube or google_drive).
     */
    public static function getVideoType(string $url): ?string
    {
        if (self::isYouTubeUrl($url)) {
            return 'youtube';
        }

        if (self::isGoogleDriveUrl($url)) {
            return 'google_drive';
        }

        return null;
    }

    /**
     * Get YouTube thumbnail URL.
     */
    public static function getYouTubeThumbnail(string $url, string $quality = 'maxresdefault'): ?string
    {
        $videoId = self::extractYouTubeId($url);
        
        if (!$videoId) {
            return null;
        }

        $qualities = ['maxresdefault', 'hqdefault', 'mqdefault', 'sddefault', 'default'];
        
        if (!in_array($quality, $qualities)) {
            $quality = 'maxresdefault';
        }

        return "https://img.youtube.com/vi/{$videoId}/{$quality}.jpg";
    }

    /**
     * Validate video URL and return type.
     */
    public static function validateVideoUrl(string $url): array
    {
        $type = self::getVideoType($url);

        if (!$type) {
            return [
                'valid' => false,
                'type' => null,
                'error' => 'URL must be a valid YouTube or Google Drive link',
            ];
        }

        return [
            'valid' => true,
            'type' => $type,
            'error' => null,
        ];
    }
}

