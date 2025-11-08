<?php

namespace App\Services;

use DOMDocument;
use DOMNode;

class HtmlSanitizer
{
    /**
     * Allowed HTML tags.
     *
     * @var array<int, string>
     */
    protected static array $allowedTags = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'a', 'ul', 'ol', 'li',
        'blockquote', 'code', 'pre', 'span', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'
    ];

    /**
     * Allowed HTML attributes for specific tags.
     *
     * @var array<string, array<int, string>>
     */
    protected static array $allowedAttributes = [
        'a' => ['href', 'target', 'rel', 'title'],
        'span' => ['class'],
        'div' => ['class'],
        'p' => ['class'],
        'ul' => ['class'],
        'ol' => ['class'],
        'li' => ['class'],
        'blockquote' => ['class'],
        'code' => ['class'],
        'pre' => ['class'],
        'h1' => ['class'],
        'h2' => ['class'],
        'h3' => ['class'],
        'h4' => ['class'],
        'h5' => ['class'],
        'h6' => ['class'],
    ];

    /**
     * Sanitize an HTML string by removing unsafe tags and attributes.
     */
    public static function sanitize(?string $html): string
    {
        $html = $html ?? '';

        if (trim($html) === '') {
            return '';
        }

        $document = new DOMDocument();
        libxml_use_internal_errors(true);

        $wrappedHtml = '<div>' . $html . '</div>';
        $document->loadHTML(
            mb_convert_encoding($wrappedHtml, 'HTML-ENTITIES', 'UTF-8'),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        $root = $document->documentElement;
        self::traverseAndSanitize($root);

        $cleanHtml = '';
        foreach ($root->childNodes as $child) {
            $cleanHtml .= $document->saveHTML($child);
        }

        libxml_clear_errors();

        return trim($cleanHtml);
    }

    /**
     * Determine if sanitized HTML is effectively empty.
     */
    public static function isEmpty(string $html): bool
    {
        return trim(strip_tags($html)) === '';
    }

    /**
     * Recursively sanitize nodes.
     */
    protected static function traverseAndSanitize(DOMNode $node): void
    {
        if ($node->nodeType === XML_ELEMENT_NODE) {
            $tag = strtolower($node->nodeName);

            if (!in_array($tag, self::$allowedTags, true)) {
                self::unwrapNode($node);
                return;
            }

            if ($node->hasAttributes()) {
                foreach (iterator_to_array($node->attributes) as $attribute) {
                    $name = strtolower($attribute->name);

                    if (!isset(self::$allowedAttributes[$tag]) || !in_array($name, self::$allowedAttributes[$tag], true)) {
                        $node->removeAttribute($attribute->name);
                        continue;
                    }

                    if ($tag === 'a' && $name === 'href' && preg_match('/^\s*javascript:/i', $attribute->value)) {
                        $node->setAttribute('href', '#');
                    }
                }
            }

            if ($tag === 'a') {
                if (!$node->hasAttribute('target')) {
                    $node->setAttribute('target', '_blank');
                }
                $node->setAttribute('rel', 'noopener noreferrer');
            }
        }

        if ($node->hasChildNodes()) {
            foreach (iterator_to_array($node->childNodes) as $child) {
                self::traverseAndSanitize($child);
            }
        }
    }

    /**
     * Replace a node with its children, effectively removing the node tag.
     */
    protected static function unwrapNode(DOMNode $node): void
    {
        $parent = $node->parentNode;

        if (!$parent) {
            return;
        }

        while ($node->firstChild) {
            $parent->insertBefore($node->firstChild, $node);
        }

        $parent->removeChild($node);
    }
}


