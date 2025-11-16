<?php

namespace App\Jobs;

use App\Services\AiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessAiRequestWithRetry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Maximum retry attempts
    public $backoff = [10, 30, 60]; // Wait 10s, 30s, 60s between retries
    public $timeout = 120; // 2 minutes timeout per attempt

    public function __construct(
        public string $type, // 'summary', 'tags', 'qa', 'embedding', etc.
        public array $data, // Request data
        public ?string $callbackClass = null, // Optional callback class
        public ?string $callbackMethod = null, // Optional callback method
        public ?array $callbackParams = null // Optional callback parameters
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AiService $aiService): void
    {
        try {
            $result = match($this->type) {
                'summary' => $this->processSummary($aiService),
                'tags' => $this->processTags($aiService),
                'qa' => $this->processQA($aiService),
                'embedding' => $this->processEmbedding($aiService),
                'topic_detection' => $this->processTopicDetection($aiService),
                'context_linking' => $this->processContextLinking($aiService),
                'build_knowledge_base' => $this->processBuildKnowledgeBase($aiService),
                default => throw new Exception("Unknown AI request type: {$this->type}"),
            };

            // Call callback if provided
            if ($this->callbackClass && $this->callbackMethod) {
                $callback = app($this->callbackClass);
                if (method_exists($callback, $this->callbackMethod)) {
                    $params = $this->callbackParams ?? [];
                    $callback->{$this->callbackMethod}($result, ...$params);
                }
            }

            Log::info('AI request processed successfully', [
                'type' => $this->type,
                'attempt' => $this->attempts(),
            ]);
        } catch (Exception $e) {
            Log::error('AI request processing failed', [
                'type' => $this->type,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Process summary request.
     */
    protected function processSummary(AiService $aiService): ?string
    {
        $content = $this->data['content'] ?? '';
        $maxLength = $this->data['max_length'] ?? 200;

        return $aiService->generateSummary($content, $maxLength);
    }

    /**
     * Process tags request.
     */
    protected function processTags(AiService $aiService): array
    {
        $content = $this->data['content'] ?? '';
        $maxTags = $this->data['max_tags'] ?? 5;

        return $aiService->suggestTags($content, $maxTags);
    }

    /**
     * Process Q&A request.
     */
    protected function processQA(AiService $aiService): ?array
    {
        $question = $this->data['question'] ?? '';
        $notes = $this->data['notes'] ?? [];
        $noteIds = $this->data['note_ids'] ?? [];

        return $aiService->answerQuestion($question, $notes, $noteIds);
    }

    /**
     * Process embedding request.
     */
    protected function processEmbedding(AiService $aiService): ?array
    {
        $content = $this->data['content'] ?? '';
        
        // Generate embedding using AI service
        // This is a placeholder - actual implementation depends on embedding API
        $embedding = $aiService->generateEmbedding($content);
        
        return [
            'embedding' => $embedding,
            'content_hash' => hash('sha256', $content),
        ];
    }

    /**
     * Process topic detection.
     */
    protected function processTopicDetection(AiService $aiService): array
    {
        $notes = $this->data['notes'] ?? [];
        $notesContent = collect($notes)->map(fn($n) => "{$n['title']}: " . substr(strip_tags($n['content'] ?? ''), 0, 300))->implode("\n");

        $prompt = "Analyze the following notes and identify the main topics/themes (list 5-10 topics, comma-separated):\n\n{$notesContent}\n\nTopics:";

        $response = $aiService->callOllama($prompt, [
            'temperature' => 0.6,
            'num_predict' => 300,
        ]);

        if ($response && isset($response['response'])) {
            $topics = $this->extractTopics($response['response']);
            return array_slice($topics, 0, 10);
        }

        return [];
    }

    /**
     * Process context linking.
     */
    protected function processContextLinking(AiService $aiService): array
    {
        $noteId = $this->data['note_id'] ?? null;
        $relatedNotes = $this->data['related_notes'] ?? [];

        if (!$noteId || empty($relatedNotes)) {
            return [];
        }

        // Use AI to find contextually related notes
        $prompt = "Given a note, identify which other notes are contextually related. Return note IDs that are related.\n\n";
        
        // This is a simplified version - full implementation would use embeddings
        return $relatedNotes; // Placeholder
    }

    /**
     * Process build knowledge base request.
     */
    protected function processBuildKnowledgeBase(AiService $aiService): array
    {
        $userId = $this->data['user_id'] ?? null;
        $workspaceId = $this->data['workspace_id'] ?? null;
        
        if (!$userId) {
            return [];
        }
        
        $user = \App\Models\User::find($userId);
        if (!$user) {
            return [];
        }
        
        $workspace = $workspaceId ? \App\Models\Workspace::find($workspaceId) : null;
        
        $aiMemoryService = app(\App\Services\AiMemoryService::class);
        $knowledgeBase = $aiMemoryService->buildKnowledgeBase($user, $workspace, 1000);
        
        return [
            'success' => true,
            'knowledge_base' => $knowledgeBase,
        ];
    }

    /**
     * Extract topics from response.
     */
    protected function extractTopics(string $response): array
    {
        $response = trim($response);
        $response = preg_replace('/^(?:topics?:|themes?:|subjects?:)\s*/i', '', $response);
        $topics = preg_split('/[,;\n]/', $response);
        
        return array_filter(
            array_map('trim', $topics),
            fn($topic) => !empty($topic) && strlen($topic) > 2 && strlen($topic) < 100
        );
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Exception $exception): void
    {
        Log::error('AI request job failed permanently', [
            'type' => $this->type,
            'attempts' => $this->attempts(),
            'error' => $exception?->getMessage(),
        ]);

        // Optionally notify user or admin about the failure
    }
}
