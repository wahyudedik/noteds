<?php

namespace App\Services;

use App\Models\Note;
use App\Models\Tag;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AutoTaggingService
{
    // AI service removed - no dependency needed

    /**
     * Auto-tag a note based on its content.
     * @deprecated AI features have been removed. This method now only uses keyword extraction.
     */
    public function autoTag(Note $note, bool $useAi = false, int $maxTags = 5): array
    {
        $tags = [];

        // Extract keywords from title and content
        $content = strip_tags($note->title . ' ' . $note->content);
        
        // AI-based tagging has been removed - only use keyword extraction
        // Method: Keyword extraction
        $keywords = $this->extractKeywords($content);
        $tags = array_merge($tags, $keywords);

        // Remove duplicates and limit
        $tags = array_unique($tags);
        $tags = array_slice($tags, 0, $maxTags);

        // Create or get tags and attach to note
        $tagModels = [];
        foreach ($tags as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName) || strlen($tagName) < 2) {
                continue;
            }

            $tag = Tag::firstOrCreate(
                ['name' => $tagName],
                ['slug' => Str::slug($tagName)]
            );

            $tagModels[] = $tag;
        }

        // Attach tags to note (sync to avoid duplicates)
        if (!empty($tagModels)) {
            $note->tags()->syncWithoutDetaching(
                collect($tagModels)->pluck('id')->toArray()
            );
        }

        return collect($tagModels)->pluck('name')->toArray();
    }

    /**
     * Extract keywords from content using simple text analysis.
     */
    protected function extractKeywords(string $content, int $maxKeywords = 10): array
    {
        // Remove HTML and normalize
        $content = strip_tags($content);
        $content = mb_strtolower($content, 'UTF-8');

        // Common stop words (Indonesian and English)
        $stopWords = [
            'yang', 'dan', 'di', 'ke', 'dari', 'untuk', 'dengan', 'pada', 'adalah', 'atau',
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with',
            'ini', 'itu', 'saya', 'kamu', 'dia', 'kita', 'mereka', 'adalah', 'akan', 'sudah',
        ];

        // Extract words (minimum 3 characters)
        preg_match_all('/\b[a-z]{3,}\b/i', $content, $matches);
        $words = $matches[0] ?? [];

        // Count word frequency
        $wordCount = array_count_values($words);

        // Remove stop words
        foreach ($stopWords as $stopWord) {
            unset($wordCount[$stopWord]);
        }

        // Sort by frequency and get top keywords
        arsort($wordCount);
        $keywords = array_keys(array_slice($wordCount, 0, $maxKeywords, true));

        return $keywords;
    }

    /**
     * Auto-tag note on creation/update.
     * @deprecated AI features have been removed. Auto-tagging is disabled.
     */
    public function autoTagOnSave(Note $note, bool $useAi = false): void
    {
        // Auto-tagging disabled - users must tag manually
        return;
    }
}

