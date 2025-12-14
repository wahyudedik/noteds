<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Input Sanitization Middleware
 * 
 * Comprehensive input sanitization to prevent:
 * - XSS (Cross-Site Scripting) attacks via input vectors
 * - HTML injection
 * - JavaScript execution in input fields
 * - Malicious control characters
 * - Null byte injection
 */
class SanitizeInput
{
    /**
     * Fields that should preserve HTML (rich text editors)
     */
    protected array $htmlFields = [
        'description',
        'content',
        'body',
        'message',
        'note_content',
        'article_body',
        'bio',
    ];

    /**
     * Fields that should never contain HTML
     */
    protected array $noHtmlFields = [
        'email',
        'phone',
        'username',
        'name',
        'title',
        'subject',
        'password',
        'password_confirmation',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->method() !== 'GET') {
            $this->sanitizeInput($request);
        }

        return $next($request);
    }

    /**
     * Sanitize all input data recursively
     */
    protected function sanitizeInput(Request $request): void
    {
        $sanitized = $this->recursiveSanitize($request->all());
        $request->merge($sanitized);
    }

    /**
     * Recursively sanitize array data
     */
    protected function recursiveSanitize(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->recursiveSanitize($value);
            } elseif (is_string($value)) {
                $sanitized[$key] = $this->sanitizeValue($key, $value);
            } else {
                $sanitized[$key] = $value; // Keep non-string values (numbers, booleans, dates)
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize individual value based on field name and content
     */
    protected function sanitizeValue(string $fieldName, string $value): string
    {
        // Never sanitize empty strings
        if (empty($value)) {
            return $value;
        }

        // Remove null bytes (null byte injection prevention)
        $value = str_replace("\0", '', $value);

        // Remove control characters except newlines and tabs
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);

        // Trim leading/trailing whitespace
        $value = trim($value);

        // Fields that should never contain HTML
        if ($this->isFieldInList($fieldName, $this->noHtmlFields)) {
            return $this->stripAllTags($value);
        }

        // HTML fields - use comprehensive HTML Purifier
        if ($this->isFieldInList($fieldName, $this->htmlFields)) {
            return $this->purifyHtml($value);
        }

        // Default: strip dangerous tags but allow safe HTML for display
        return $this->stripDangerousTags($value);
    }

    /**
     * Strip all HTML tags, entities, and dangerous scripts
     */
    protected function stripAllTags(string $value): string
    {
        // Remove all HTML tags
        $value = strip_tags($value);

        // Decode HTML entities but keep spaces
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Trim whitespace again after decoding
        return trim($value);
    }

    /**
     * Strip only dangerous tags (script, iframe, onclick, etc)
     */
    protected function stripDangerousTags(string $value): string
    {
        // Dangerous patterns to remove
        $patterns = [
            // Script tags with content
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i',
            // Event handlers (onclick, onload, onerror, etc)
            '/on\w+\s*=\s*["\']?(?:[^"\'\s>]|\\["\'])*["\']?/i',
            // iframes
            '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/i',
            // object and embed tags
            '/<(?:object|embed)\b[^<]*(?:(?!<\/(?:object|embed)>)<[^<]*)*<\/(?:object|embed)>/i',
            // form tags and inputs
            '/<(?:form|input|button)\b[^<]*(?:(?!<\/(?:form|input|button)>)<[^<]*)*<\/(?:form|input|button)>/i',
            // style tags with content
            '/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/i',
            // meta tags
            '/<meta\b[^>]*>/i',
            // javascript protocol
            '/javascript:/i',
            // data protocol (XSS)
            '/data:text\/html/i',
        ];

        foreach ($patterns as $pattern) {
            $value = preg_replace($pattern, '', $value);
        }

        return trim($value);
    }

    /**
     * Comprehensive HTML purification
     */
    protected function purifyHtml(string $value): string
    {
        // Use HTML Purifier if available, otherwise fall back to strip_tags
        if (class_exists('HTMLPurifier')) {
            $config = \HTMLPurifier_Config::createDefault();

            // Allow safe HTML elements for rich text
            $config->set('HTML.Allowed', implode(',', [
                'p',
                'br',
                'div',
                'span',
                'b',
                'strong',
                'i',
                'em',
                'u',
                's',
                'h1',
                'h2',
                'h3',
                'h4',
                'h5',
                'h6',
                'ul',
                'ol',
                'li',
                'blockquote',
                'code',
                'pre',
                'a[href|title|target|rel]',
                'img[src|alt|width|height|title]',
                'table',
                'thead',
                'tbody',
                'tr',
                'td',
                'th',
            ]));

            // Allow safe protocols
            $config->set('URI.AllowedSchemes', [
                'http' => true,
                'https' => true,
                'mailto' => true,
                'ftp' => true,
            ]);

            // Disable dangerous features
            $config->set('HTML.Trusted', false);
            $config->set('CSS.AllowedProperties', []);
            $config->set('HTML.FlashAllowFullScreen', false);

            $purifier = new \HTMLPurifier($config);
            return $purifier->purify($value);
        }

        // Fallback if HTMLPurifier not available
        return $this->stripDangerousTags($value);
    }

    /**
     * Check if field name matches any pattern in the provided list
     */
    protected function isFieldInList(string $fieldName, array $fieldList): bool
    {
        // Exact match
        if (in_array($fieldName, $fieldList, true)) {
            return true;
        }

        // Check with array notation (e.g., "notes[0].content" -> "content")
        foreach ($fieldList as $field) {
            if (preg_match('/[\.\[]' . preg_quote($field) . '[\]\.]?$/i', $fieldName)) {
                return true;
            }
        }

        return false;
    }
}
