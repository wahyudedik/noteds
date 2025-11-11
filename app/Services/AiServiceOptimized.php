<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Optimized AI Service with caching and performance improvements
 */
class AiServiceOptimized extends AiService
{
    /**
     * Cache duration for AI responses (in seconds)
     * Default: 24 hours (86400 seconds)
     */
    protected int $cacheDuration = 86400;

    /**
     * Generate a summary with caching
     */
    public function generateSummary(string $content, int $maxLength = 200): ?string
    {
        // Create cache key based on content hash
        $cacheKey = 'ai_summary_' . md5($content . '_' . $maxLength);

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($content, $maxLength) {
            return parent::generateSummary($content, $maxLength);
        });
    }

    /**
     * Suggest tags with caching
     */
    public function suggestTags(string $content, int $maxTags = 5): array
    {
        // Create cache key based on content hash
        $cacheKey = 'ai_tags_' . md5($content . '_' . $maxTags);

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($content, $maxTags) {
            return parent::suggestTags($content, $maxTags);
        });
    }

    /**
     * Answer question with caching (cache based on question + notes content hash)
     */
    public function answerQuestion(string $question, array $notes, array $noteIds = []): ?array
    {
        // Create cache key based on question and notes content
        $notesHash = md5(implode('|', array_map(fn($n) => substr($n, 0, 500), $notes)));
        $cacheKey = 'ai_qa_' . md5($question . '_' . $notesHash);

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($question, $notes, $noteIds) {
            return parent::answerQuestion($question, $notes, $noteIds);
        });
    }

    /**
     * Optimized callOllama with caching, connection pooling, and CPU optimization
     * Inherits CPU optimization from parent class (AiService)
     */
    public function callOllama(string $prompt, array $options = []): ?array
    {
        // Create cache key for identical prompts (only for read operations)
        $isReadOperation = !isset($options['stream']) || $options['stream'] === false;
        
        if ($isReadOperation) {
            // Create cache key (CPU optimization is handled by parent class)
            // Note: Cache key doesn't include CPU settings as they're consistent per server
            $cacheKey = 'ollama_response_' . md5($prompt . '_' . json_encode($options));
            
            return Cache::remember($cacheKey, $this->cacheDuration, function () use ($prompt, $options) {
                // Parent class (AiService) already handles CPU optimization automatically
                return parent::callOllama($prompt, $options);
            });
        }

        // For streaming operations, don't cache but still use CPU optimization
        // Parent class (AiService) already handles CPU optimization automatically
        return parent::callOllama($prompt, $options);
    }

    /**
     * Check if Ollama is available (with caching to avoid repeated checks)
     */
    public function isAvailable(): bool
    {
        return Cache::remember('ollama_available', 60, function () {
            return parent::isAvailable();
        });
    }

    /**
     * Set cache duration
     */
    public function setCacheDuration(int $seconds): void
    {
        $this->cacheDuration = $seconds;
    }

    /**
     * Clear all AI-related cache
     */
    public function clearCache(): void
    {
        Cache::flush(); // Or use specific tags if using tagged cache
    }
}

