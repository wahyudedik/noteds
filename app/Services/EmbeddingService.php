<?php

namespace App\Services;

use App\Models\Note;
use App\Models\NoteEmbedding;
use App\Services\AiService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EmbeddingService
{
    public function __construct(
        protected AiService $aiService
    ) {}

    /**
     * Generate and store embedding for a note.
     */
    public function generateAndStoreEmbedding(Note $note): ?NoteEmbedding
    {
        try {
            $content = strip_tags($note->title . ' ' . $note->content);
            $contentHash = hash('sha256', $content);

            // Check if embedding already exists and content hasn't changed
            $existing = NoteEmbedding::where('note_id', $note->id)
                ->where('content_hash', $contentHash)
                ->first();

            if ($existing) {
                return $existing;
            }

            // Generate embedding
            $embedding = $this->aiService->generateEmbedding($content);

            if (!$embedding) {
                return null;
            }

            // Delete old embedding if exists
            NoteEmbedding::where('note_id', $note->id)->delete();

            // Store new embedding
            return NoteEmbedding::create([
                'note_id' => $note->id,
                'content_hash' => $contentHash,
                'embedding' => $embedding,
                'dimension' => count($embedding),
                'model' => config('services.ollama.model', 'llama3.2'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate embedding', [
                'note_id' => $note->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Find similar notes using embedding similarity.
     */
    public function findSimilarNotes(Note $note, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        $noteEmbedding = NoteEmbedding::where('note_id', $note->id)->first();

        if (!$noteEmbedding) {
            // Generate embedding if doesn't exist
            $noteEmbedding = $this->generateAndStoreEmbedding($note);
            if (!$noteEmbedding) {
                return collect();
            }
        }

        // Find similar notes using cosine similarity
        $allEmbeddings = NoteEmbedding::where('note_id', '!=', $note->id)
            ->where('dimension', $noteEmbedding->dimension)
            ->get();

        $similarities = [];
        foreach ($allEmbeddings as $other) {
            $similarity = $noteEmbedding->similarity($other);
            if ($similarity > 0.3) { // Threshold for similarity
                $similarities[$other->note_id] = $similarity;
            }
        }

        // Sort by similarity and get top notes
        arsort($similarities);
        $similarNoteIds = array_slice(array_keys($similarities), 0, $limit);

        return Note::whereIn('id', $similarNoteIds)
            ->orderByRaw('FIELD(id, ' . implode(',', array_map(fn($id) => "'{$id}'", $similarNoteIds)) . ')')
            ->get();
    }

    /**
     * Semantic search using embeddings.
     */
    public function semanticSearch(string $query, ?\App\Models\User $user = null, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        try {
            // Generate embedding for query
            $queryEmbedding = $this->aiService->generateEmbedding($query);

            if (!$queryEmbedding) {
                return collect();
            }

            // Get all note embeddings
            $queryBuilder = NoteEmbedding::query();
            
            if ($user) {
                // Filter by user's notes or public notes
                $queryBuilder->whereHas('note', function($q) use ($user) {
                    $q->where(function($subQ) use ($user) {
                        $subQ->where('user_id', $user->id)
                             ->orWhere('is_public', true);
                    });
                });
            } else {
                // Only public notes
                $queryBuilder->whereHas('note', function($q) {
                    $q->where('is_public', true);
                });
            }

            $embeddings = $queryBuilder->get();

            // Calculate similarities
            $similarities = [];
            foreach ($embeddings as $embedding) {
                // Create temporary embedding model for similarity calculation
                $tempEmbedding = new NoteEmbedding([
                    'embedding' => $queryEmbedding,
                    'dimension' => count($queryEmbedding),
                ]);
                
                $similarity = $tempEmbedding->similarity($embedding);
                if ($similarity > 0.2) { // Threshold
                    $similarities[$embedding->note_id] = $similarity;
                }
            }

            // Sort by similarity
            arsort($similarities);
            $noteIds = array_slice(array_keys($similarities), 0, $limit);

            return Note::whereIn('id', $noteIds)
                ->orderByRaw('FIELD(id, ' . implode(',', array_map(fn($id) => "'{$id}'", $noteIds)) . ')')
                ->get();
        } catch (\Exception $e) {
            Log::error('Semantic search failed', [
                'error' => $e->getMessage(),
                'query' => $query,
            ]);

            return collect();
        }
    }
}

