<?php

namespace App\Services;

use App\Models\Note;
use App\Models\User;
use App\Services\EmbeddingService;
use App\Services\AiService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ContextLinkingService
{
    public function __construct(
        protected EmbeddingService $embeddingService,
        protected AiService $aiService
    ) {}

    /**
     * Find contextually related notes for a given note.
     */
    public function findRelatedNotes(Note $note, ?User $user = null, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        try {
            // Method 1: Use embedding similarity
            $similarNotes = $this->embeddingService->findSimilarNotes($note, $limit);
            
            // Method 2: Use AI to detect context links
            $aiRelated = $this->findRelatedByAI($note, $user, $limit);
            
            // Combine and deduplicate
            $allRelated = $similarNotes->merge($aiRelated)->unique('id');
            
            return $allRelated->take($limit);
        } catch (\Exception $e) {
            Log::error('Context linking failed', [
                'note_id' => $note->id,
                'error' => $e->getMessage(),
            ]);

            return collect();
        }
    }

    /**
     * Find related notes using AI context understanding.
     */
    protected function findRelatedByAI(Note $note, ?User $user = null, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        try {
            // Get candidate notes
            $query = Note::query();
            
            if ($user) {
                $query->where(function($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('is_public', true);
                });
            } else {
                $query->where('is_public', true);
            }

            $candidates = $query->where('id', '!=', $note->id)
                ->limit(50) // Limit candidates for AI processing
                ->get(['id', 'title', 'content']);

            if ($candidates->isEmpty()) {
                return collect();
            }

            // Prepare content for AI
            $noteContent = substr(strip_tags($note->title . ' ' . $note->content), 0, 1000);
            $candidatesContent = $candidates->map(function($c) {
                return "ID: {$c->id}\nTitle: {$c->title}\nContent: " . substr(strip_tags($c->content), 0, 300);
            })->implode("\n\n---\n\n");

            $prompt = "Given this note:\n\n{$noteContent}\n\nWhich of these notes are contextually related? Return only the note IDs (comma-separated) that are related:\n\n{$candidatesContent}\n\nRelated note IDs:";

            $response = $this->aiService->callOllama($prompt, [
                'temperature' => 0.3,
                'num_predict' => 500,
            ]);

            if ($response && isset($response['response'])) {
                // Extract note IDs from response
                preg_match_all('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i', $response['response'], $matches);
                
                if (!empty($matches[0])) {
                    $relatedIds = array_unique($matches[0]);
                    return Note::whereIn('id', array_slice($relatedIds, 0, $limit))->get();
                }
            }

            return collect();
        } catch (\Exception $e) {
            Log::error('AI context linking failed', [
                'note_id' => $note->id,
                'error' => $e->getMessage(),
            ]);

            return collect();
        }
    }

    /**
     * Create context links between notes (store relationships).
     */
    public function createContextLinks(Note $note, array $relatedNoteIds): void
    {
        // This could store relationships in a separate table for faster retrieval
        // For now, we calculate on-the-fly using embeddings
    }
}

