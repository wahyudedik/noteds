<?php

namespace App\Jobs;

use App\Services\AiService;
use App\Services\AiUsageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Process AI requests asynchronously for high traffic scenarios.
 * This job handles long-running AI operations that might block the main request.
 */
class ProcessAiRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $type, // 'analyze', 'ask', 'generate_image', 'generate_video', etc.
        public int $userId,
        public array $data, // Request data (prompt, content, options, etc.)
        public ?string $cacheKey = null, // Optional cache key for result
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AiService $aiService, AiUsageService $aiUsageService): void
    {
        $startTime = microtime(true);
        
        try {
            Log::info("Processing AI request", [
                'type' => $this->type,
                'user_id' => $this->userId,
                'job_id' => $this->job->getJobId(),
            ]);

            $result = match ($this->type) {
                'analyze' => $this->handleAnalyze($aiService),
                'ask' => $this->handleAsk($aiService),
                'generate_image' => $this->handleGenerateImage($aiService, $aiUsageService),
                'generate_video' => $this->handleGenerateVideo($aiService, $aiUsageService),
                'search_images' => $this->handleSearchImages($aiService, $aiUsageService),
                'semantic_search' => $this->handleSemanticSearch($aiService),
                'context_links' => $this->handleContextLinks($aiService),
                'generate_content' => $this->handleGenerateContent($aiService),
                'generate_ideas' => $this->handleGenerateIdeas($aiService),
                default => throw new \InvalidArgumentException("Unknown AI request type: {$this->type}"),
            };

            // Cache result if cache key provided
            if ($this->cacheKey && $result) {
                Cache::put($this->cacheKey, $result, now()->addHours(24));
            }

            $duration = microtime(true) - $startTime;
            
            Log::info("AI request completed", [
                'type' => $this->type,
                'user_id' => $this->userId,
                'duration' => round($duration, 2),
                'success' => true,
            ]);

        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;
            
            Log::error("AI request failed", [
                'type' => $this->type,
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'duration' => round($duration, 2),
            ]);

            throw $e; // Re-throw to trigger retry mechanism
        }
    }

    /**
     * Handle analyze request.
     */
    protected function handleAnalyze(AiService $aiService): array
    {
        $content = $this->data['content'] ?? '';
        $summary = $aiService->generateSummary($content, 200);
        $tags = $aiService->suggestTags($content, 5);

        return [
            'summary' => $summary,
            'tags' => $tags,
        ];
    }

    /**
     * Handle ask request.
     */
    protected function handleAsk(AiService $aiService): array
    {
        $question = $this->data['question'] ?? '';
        $noteContents = $this->data['note_contents'] ?? [];
        $noteIds = $this->data['note_ids'] ?? [];

        $result = $aiService->answerQuestion($question, $noteContents, $noteIds);

        return $result ?? [
            'answer' => 'Tidak dapat menghasilkan jawaban.',
            'referenced_note_ids' => [],
        ];
    }

    /**
     * Handle generate image request.
     */
    protected function handleGenerateImage(AiService $aiService, AiUsageService $aiUsageService): ?array
    {
        $prompt = $this->data['prompt'] ?? '';
        $options = $this->data['options'] ?? [];

        return $aiService->generateImage($prompt, $options);
    }

    /**
     * Handle generate video request.
     */
    protected function handleGenerateVideo(AiService $aiService, AiUsageService $aiUsageService): ?array
    {
        $prompt = $this->data['prompt'] ?? '';
        $options = $this->data['options'] ?? [];

        return $aiService->generateVideo($prompt, $options);
    }

    /**
     * Handle search images request.
     */
    protected function handleSearchImages(AiService $aiService, AiUsageService $aiUsageService): array
    {
        $query = $this->data['query'] ?? '';
        $limit = $this->data['limit'] ?? 10;

        return $aiService->searchImages($query, $limit);
    }

    /**
     * Handle semantic search request.
     */
    protected function handleSemanticSearch(AiService $aiService): array
    {
        $query = $this->data['query'] ?? '';
        $notesData = $this->data['notes_data'] ?? [];

        $relevantIds = $aiService->semanticSearch($query, $notesData);

        return [
            'relevant_ids' => $relevantIds,
        ];
    }

    /**
     * Handle context links request.
     */
    protected function handleContextLinks(AiService $aiService): array
    {
        $notesData = $this->data['notes_data'] ?? [];
        $focusNoteId = $this->data['focus_note_id'] ?? null;

        return $aiService->detectContextLinks($notesData, $focusNoteId);
    }

    /**
     * Handle generate content request.
     */
    protected function handleGenerateContent(AiService $aiService): ?string
    {
        $prompt = $this->data['prompt'] ?? '';
        $maxLength = $this->data['max_length'] ?? 2000;

        return $aiService->generateContent($prompt, $maxLength);
    }

    /**
     * Handle generate ideas request.
     */
    protected function handleGenerateIdeas(AiService $aiService): array
    {
        $topic = $this->data['topic'] ?? '';
        $count = $this->data['count'] ?? 5;

        return $aiService->generateIdeas($topic, $count);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("AI request job failed permanently", [
            'type' => $this->type,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
            'job_id' => $this->job->getJobId(),
        ]);
    }
}

