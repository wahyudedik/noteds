<?php

namespace App\Services;

use App\Models\Note;
use App\Models\Tag;
use App\Services\AiService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AutoTaggingService
{
    public function __construct(
        protected AiService $aiService
    ) {}

    /**
     * Auto-tag a note based on its content.
     * Premium feature - uses AI to extract relevant tags.
     */
    public function autoTag(Note $note, bool $useAi = true, int $maxTags = 5): array
    {
        $tags = [];

        // Extract keywords from title and content
        $content = strip_tags($note->title . ' ' . $note->content);
        
        // Method 1: AI-based tagging (premium feature)
        if ($useAi && $this->aiService->isAvailable()) {
            try {
                $aiTags = $this->aiService->suggestTags($content, $maxTags);
                $tags = array_merge($tags, $aiTags);
            } catch (\Exception $e) {
                Log::warning('AI tagging failed, falling back to keyword extraction', [
                    'note_id' => $note->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Method 2: Keyword extraction (fallback or supplement)
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
     */
    public function autoTagOnSave(Note $note, bool $useAi = true): void
    {
        // Only auto-tag if note has content
        if (empty($note->content) && empty($note->title)) {
            return;
        }

        // Check if note already has tags (don't override manual tags)
        if ($note->tags()->count() > 0) {
            return; // User has already tagged, don't auto-tag
        }

        $this->autoTag($note, $useAi);
    }
}

