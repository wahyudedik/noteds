<?php

namespace App\Services;

use App\Models\Note;
use App\Models\User;
use App\Models\Workspace;
use App\Services\AiService;
use App\Services\EmbeddingService;
use App\Services\ContextLinkingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AiMemoryService
{
    public function __construct(
        protected AiService $aiService,
        protected EmbeddingService $embeddingService,
        protected ContextLinkingService $contextLinkingService
    ) {}

    /**
     * Build AI knowledge base from user's notes.
     * This creates a comprehensive knowledge base that AI can use to answer questions.
     */
    public function buildKnowledgeBase(User $user, ?Workspace $workspace = null, int $limit = 1000): array
    {
        try {
            $cacheKey = "ai_knowledge_base:{$user->id}:" . ($workspace ? $workspace->id : 'all');
            
            return Cache::remember($cacheKey, now()->addHours(24), function() use ($user, $workspace, $limit) {
                $query = $user->notes();
                
                if ($workspace) {
                    $query->where('workspace_id', $workspace->id);
                }
                
                $notes = $query->where('status', 'active')
                    ->limit($limit)
                    ->get(['id', 'title', 'content', 'summary', 'created_at', 'updated_at', 'workspace_id', 'folder_id']);
                
                // Build structured knowledge base
                $knowledgeBase = [
                    'user_id' => $user->id,
                    'workspace_id' => $workspace?->id,
                    'total_notes' => $notes->count(),
                    'notes' => [],
                    'topics' => [],
                    'timeline' => [],
                ];
                
                foreach ($notes as $note) {
                    $content = strip_tags($note->content);
                    $knowledgeBase['notes'][] = [
                        'id' => $note->id,
                        'title' => $note->title,
                        'content' => substr($content, 0, 2000), // Limit content
                        'summary' => $note->summary,
                        'created_at' => $note->created_at->toIso8601String(),
                        'updated_at' => $note->updated_at->toIso8601String(),
                    ];
                    
                    // Extract topics from tags
                    $tags = $note->tags()->pluck('name')->toArray();
                    if (!empty($tags)) {
                        $knowledgeBase['topics'] = array_merge($knowledgeBase['topics'], $tags);
                    }
                    
                    // Timeline entry
                    $knowledgeBase['timeline'][] = [
                        'date' => $note->created_at->format('Y-m-d'),
                        'action' => 'created',
                        'note_id' => $note->id,
                        'title' => $note->title,
                    ];
                }
                
                // Remove duplicate topics
                $knowledgeBase['topics'] = array_unique($knowledgeBase['topics']);
                
                return $knowledgeBase;
            });
        } catch (\Exception $e) {
            Log::error('Failed to build knowledge base', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return [];
        }
    }

    /**
     * Answer question using knowledge base from user's notes.
     * AI will use all notes as context to provide intelligent answers.
     */
    public function answerWithKnowledgeBase(User $user, string $question, ?Workspace $workspace = null, int $maxNotes = 50): ?array
    {
        try {
            if (!$this->aiService->isAvailable()) {
                return [
                    'success' => false,
                    'message' => 'AI service tidak tersedia saat ini.',
                ];
            }

            // Build knowledge base
            $knowledgeBase = $this->buildKnowledgeBase($user, $workspace, $maxNotes);
            
            if (empty($knowledgeBase['notes'])) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada catatan yang tersedia untuk menjawab pertanyaan.',
                ];
            }

            // Prepare context for AI
            $context = $this->prepareContext($knowledgeBase, $question);
            
            // Use AI to answer question with context
            $prompt = $this->buildPrompt($question, $context, $knowledgeBase);
            
            $response = $this->aiService->callOllama($prompt, [
                'temperature' => 0.5, // Lower temperature for more factual answers
                'num_predict' => 1000,
            ]);

            if ($response && isset($response['response'])) {
                $answer = trim($response['response']);
                
                // Extract referenced note IDs from answer
                $referencedNoteIds = $this->extractReferencedNotes($answer, $knowledgeBase);
                
                return [
                    'success' => true,
                    'answer' => $answer,
                    'referenced_notes' => $referencedNoteIds,
                    'knowledge_base_stats' => [
                        'total_notes' => $knowledgeBase['total_notes'],
                        'topics_count' => count($knowledgeBase['topics']),
                    ],
                ];
            }

            return [
                'success' => false,
                'message' => 'Tidak dapat menghasilkan jawaban.',
            ];
        } catch (\Exception $e) {
            Log::error('AI Memory answer failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses pertanyaan.',
            ];
        }
    }

    /**
     * Prepare context from knowledge base for AI.
     */
    protected function prepareContext(array $knowledgeBase, string $question): string
    {
        // Find most relevant notes using semantic search
        $relevantNotes = [];
        
        // Simple keyword matching for now (can be enhanced with embeddings)
        $questionKeywords = $this->extractKeywords($question);
        
        foreach ($knowledgeBase['notes'] as $note) {
            $relevance = 0;
            $noteText = mb_strtolower($note['title'] . ' ' . $note['content'], 'UTF-8');
            
            foreach ($questionKeywords as $keyword) {
                if (strpos($noteText, mb_strtolower($keyword, 'UTF-8')) !== false) {
                    $relevance++;
                }
            }
            
            if ($relevance > 0) {
                $relevantNotes[] = [
                    'relevance' => $relevance,
                    'note' => $note,
                ];
            }
        }
        
        // Sort by relevance and take top 20
        usort($relevantNotes, fn($a, $b) => $b['relevance'] <=> $a['relevance']);
        $relevantNotes = array_slice($relevantNotes, 0, 20);
        
        // Build context string
        $context = "User memiliki {$knowledgeBase['total_notes']} catatan dengan topik: " . implode(', ', array_slice($knowledgeBase['topics'], 0, 20)) . "\n\n";
        $context .= "Catatan yang relevan:\n\n";
        
        foreach ($relevantNotes as $index => $item) {
            $note = $item['note'];
            $context .= "Catatan " . ($index + 1) . " (ID: {$note['id']}):\n";
            $context .= "Judul: {$note['title']}\n";
            if (!empty($note['summary'])) {
                $context .= "Ringkasan: {$note['summary']}\n";
            }
            $context .= "Isi: " . substr($note['content'], 0, 1000) . "\n\n";
        }
        
        return $context;
    }

    /**
     * Build prompt for AI with knowledge base context.
     */
    protected function buildPrompt(string $question, string $context, array $knowledgeBase): string
    {
        $prompt = "Anda adalah AI assistant yang membantu pengguna memahami dan mengakses catatan mereka.\n\n";
        $prompt .= "Pengguna memiliki {$knowledgeBase['total_notes']} catatan yang berisi pengetahuan dan informasi penting mereka.\n\n";
        $prompt .= "Gunakan konteks berikut untuk menjawab pertanyaan pengguna dengan akurat dan relevan:\n\n";
        $prompt .= $context . "\n\n";
        $prompt .= "Pertanyaan pengguna: {$question}\n\n";
        $prompt .= "Instruksi:\n";
        $prompt .= "1. Jawab pertanyaan berdasarkan informasi dari catatan di atas\n";
        $prompt .= "2. Jika informasi tidak ditemukan di catatan, katakan dengan jujur\n";
        $prompt .= "3. Sebutkan ID catatan yang relevan jika memungkinkan (format: Catatan X)\n";
        $prompt .= "4. Berikan jawaban yang jelas, terstruktur, dan mudah dipahami\n";
        $prompt .= "5. Jika ada beberapa catatan yang relevan, gabungkan informasinya\n\n";
        $prompt .= "Jawaban:";
        
        return $prompt;
    }

    /**
     * Extract referenced note IDs from AI answer.
     */
    protected function extractReferencedNotes(string $answer, array $knowledgeBase): array
    {
        $referencedIds = [];
        
        // Look for "Catatan X" pattern
        preg_match_all('/Catatan\s+(\d+)/i', $answer, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $noteIndex) {
                $index = (int)$noteIndex - 1; // Convert to 0-based index
                if (isset($knowledgeBase['notes'][$index])) {
                    $referencedIds[] = $knowledgeBase['notes'][$index]['id'];
                }
            }
        }
        
        // Also look for UUIDs directly
        preg_match_all('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i', $answer, $uuidMatches);
        if (!empty($uuidMatches[0])) {
            $referencedIds = array_merge($referencedIds, $uuidMatches[0]);
        }
        
        return array_unique($referencedIds);
    }

    /**
     * Extract keywords from question.
     */
    protected function extractKeywords(string $text): array
    {
        $text = mb_strtolower($text, 'UTF-8');
        $stopWords = ['yang', 'dan', 'di', 'ke', 'dari', 'untuk', 'dengan', 'pada', 'adalah', 'atau', 'ini', 'itu', 'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with'];
        
        preg_match_all('/\b[a-z]{3,}\b/i', $text, $matches);
        $keywords = array_filter($matches[0], fn($word) => !in_array($word, $stopWords));
        
        return array_unique(array_values($keywords));
    }

    /**
     * Find contextually linked notes across all user's notes.
     */
    public function findContextualLinks(User $user, Note $focusNote, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        try {
            // Use embedding similarity
            $similarNotes = $this->embeddingService->findSimilarNotes($focusNote, $limit);
            
            // Also use AI to find semantic links
            $userNotes = $user->notes()
                ->where('id', '!=', $focusNote->id)
                ->where('status', 'active')
                ->limit(100)
                ->get(['id', 'title', 'content']);
            
            if ($userNotes->isNotEmpty()) {
                $aiLinked = $this->contextLinkingService->findRelatedByAI($focusNote, $user, $limit);
                $similarNotes = $similarNotes->merge($aiLinked)->unique('id');
            }
            
            return $similarNotes->take($limit);
        } catch (\Exception $e) {
            Log::error('Context linking failed', [
                'user_id' => $user->id,
                'note_id' => $focusNote->id,
                'error' => $e->getMessage(),
            ]);
            
            return collect();
        }
    }

    /**
     * Generate insights from user's notes using AI.
     */
    public function generateInsights(User $user, ?Workspace $workspace = null): array
    {
        try {
            $knowledgeBase = $this->buildKnowledgeBase($user, $workspace, 500);
            
            if (empty($knowledgeBase['notes'])) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada catatan yang cukup untuk menghasilkan insights.',
                ];
            }

            // Prepare summary of notes
            $notesSummary = collect($knowledgeBase['notes'])->map(function($note) {
                return "{$note['title']}: " . substr($note['content'], 0, 200);
            })->implode("\n");

            $prompt = "Berdasarkan catatan pengguna berikut, berikan insights dan analisis:\n\n";
            $prompt .= "1. Topik-topik utama yang sering dibahas\n";
            $prompt .= "2. Pola atau tren yang terlihat\n";
            $prompt .= "3. Rekomendasi atau saran berdasarkan konten\n";
            $prompt .= "4. Koneksi atau hubungan antar topik\n\n";
            $prompt .= "Catatan:\n{$notesSummary}\n\n";
            $prompt .= "Insights:";

            $response = $this->aiService->callOllama($prompt, [
                'temperature' => 0.7,
                'num_predict' => 1500,
            ]);

            if ($response && isset($response['response'])) {
                return [
                    'success' => true,
                    'insights' => trim($response['response']),
                    'stats' => [
                        'total_notes' => $knowledgeBase['total_notes'],
                        'topics' => array_slice($knowledgeBase['topics'], 0, 20),
                    ],
                ];
            }

            return [
                'success' => false,
                'message' => 'Tidak dapat menghasilkan insights.',
            ];
        } catch (\Exception $e) {
            Log::error('AI insights generation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghasilkan insights.',
            ];
        }
    }

    /**
     * Train AI model with user's notes (for future fine-tuning).
     * This prepares data for training but doesn't actually train (requires training infrastructure).
     */
    public function prepareTrainingData(User $user, ?Workspace $workspace = null): array
    {
        try {
            $knowledgeBase = $this->buildKnowledgeBase($user, $workspace, 10000);
            
            // Format data for training (conversational format)
            $trainingData = [];
            
            foreach ($knowledgeBase['notes'] as $note) {
                // Create Q&A pairs from notes
                $trainingData[] = [
                    'instruction' => "Berdasarkan catatan berikut, jawab pertanyaan tentang: {$note['title']}",
                    'input' => $note['content'],
                    'output' => $note['summary'] ?? substr(strip_tags($note['content']), 0, 500),
                ];
            }
            
            return [
                'success' => true,
                'total_samples' => count($trainingData),
                'data' => $trainingData,
                'format' => 'conversational',
            ];
        } catch (\Exception $e) {
            Log::error('Training data preparation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menyiapkan data training.',
            ];
        }
    }
}

