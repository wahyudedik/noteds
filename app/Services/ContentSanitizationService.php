<?php

namespace App\Services;

class ContentSanitizationService
{
    /**
     * Allowed HTML tags for post content.
     */
    private const ALLOWED_TAGS = '<p><br><strong><b><em><i><u><ul><ol><li><a><h1><h2><h3><h4><h5><h6><blockquote><code><pre>';

    /**
     * Sanitize HTML content.
     * 
     * Note: For production, consider installing ezyang/htmlpurifier for better security.
     *
     * @param string $content
     * @return string
     */
    public function sanitize(string $content): string
    {
        if (empty($content)) {
            return '';
        }

        // Strip all tags except allowed ones
        $sanitized = strip_tags($content, self::ALLOWED_TAGS);

        // Remove javascript: and data: protocols from links
        $sanitized = preg_replace_callback(
            '/<a\s+[^>]*href=["\']([^"\']*)["\'][^>]*>/i',
            function ($matches) {
                $url = $matches[1];
                // Only allow http, https, and relative URLs
                if (preg_match('/^(https?:\/\/|\/|#)/i', $url)) {
                    return $matches[0];
                }
                return '';
            },
            $sanitized
        );

        // Remove any remaining script tags and event handlers
        $sanitized = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi', '', $sanitized);
        $sanitized = preg_replace('/on\w+="[^"]*"/i', '', $sanitized);
        $sanitized = preg_replace('/on\w+=\'[^\']*\'/i', '', $sanitized);

        return trim($sanitized);
    }

    /**
     * Strip all HTML tags.
     *
     * @param string $content
     * @return string
     */
    public function stripTags(string $content): string
    {
        return strip_tags($content);
    }
}

