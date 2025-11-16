<?php

namespace App\Services;

use App\Models\Note;
use App\Models\User;
use App\Services\AiService;
use Illuminate\Support\Facades\Log;
use Exception;

class NoteQAService
{
    public function __construct(
        protected AiService $aiService
    ) {}

    /**
     * Answer a question about a specific note using Natural Language.
     * Premium feature.
     */
    public function answerQuestion(Note $note, string $question, ?User $user = null): ?array
    {
        try {
            // Check if user has access to note
            if ($user && $note->user_id !== $user->id && !$note->is_public) {
                return [
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke catatan ini.',
                ];
            }

            // Check if AI is available
            if (!$this->aiService->isAvailable()) {
                return [
                    'success' => false,
                    'message' => 'AI service sedang tidak tersedia. Silakan coba lagi nanti.',
                ];
            }

            $content = strip_tags($note->content);
            $content = substr($content, 0, 5000); // Limit content for AI processing

            $prompt = "Based on the following note, answer this question: \"{$question}\"\n\nNote Title: {$note->title}\n\nNote Content:\n{$content}\n\nAnswer the question concisely and accurately based only on the information in the note:";

            $response = $this->aiService->callOllama($prompt, [
                'temperature' => 0.5, // Lower temperature for more accurate answers
                'num_predict' => 500,
            ]);

            if ($response && isset($response['response'])) {
                $answer = trim($response['response']);
                
                return [
                    'success' => true,
                    'question' => $question,
                    'answer' => $answer,
                    'note_id' => $note->id,
                    'note_title' => $note->title,
                ];
            }

            return [
                'success' => false,
                'message' => 'Tidak dapat menghasilkan jawaban. Silakan coba lagi.',
            ];
        } catch (Exception $e) {
            Log::error('Note Q&A failed', [
                'error' => $e->getMessage(),
                'note_id' => $note->id,
                'question' => $question,
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses pertanyaan.',
            ];
        }
    }

    /**
     * Answer questions about multiple notes (user's notes).
     * Premium feature.
     */
    public function answerQuestionAboutNotes(User $user, string $question, array $noteIds = [], int $limit = 10): ?array
    {
        try {
            if (!$this->aiService->isAvailable()) {
                return [
                    'success' => false,
                    'message' => 'AI service sedang tidak tersedia.',
                ];
            }

            // Get user's notes
            $notesQuery = $user->notes();
            
            if (!empty($noteIds)) {
                $notesQuery->whereIn('id', $noteIds);
            }

            $notes = $notesQuery->limit($limit)->get(['id', 'title', 'content']);

            if ($notes->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada catatan yang ditemukan.',
                ];
            }

            // Prepare notes content
            $notesContent = $notes->map(function ($note) {
                $content = strip_tags($note->content);
                return "Title: {$note->title}\nContent: " . substr($content, 0, 1000);
            })->implode("\n\n---\n\n");

            $prompt = "Based on the following notes, answer this question: \"{$question}\"\n\nNotes:\n{$notesContent}\n\nProvide a comprehensive answer based on the information in these notes:";

            $response = $this->aiService->callOllama($prompt, [
                'temperature' => 0.5,
                'num_predict' => 800,
            ]);

            if ($response && isset($response['response'])) {
                $answer = trim($response['response']);
                
                return [
                    'success' => true,
                    'question' => $question,
                    'answer' => $answer,
                    'notes_count' => $notes->count(),
                    'notes' => $notes->map(fn($n) => ['id' => $n->id, 'title' => $n->title])->toArray(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Tidak dapat menghasilkan jawaban.',
            ];
        } catch (Exception $e) {
            Log::error('Multi-note Q&A failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'question' => $question,
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses pertanyaan.',
            ];
        }
    }

    /**
     * Suggest questions based on note content.
     */
    public function suggestQuestions(Note $note, int $maxQuestions = 5): array
    {
        try {
            if (!$this->aiService->isAvailable()) {
                return [];
            }

            $content = strip_tags($note->content);
            $content = substr($content, 0, 3000);

            $prompt = "Based on the following note, suggest {$maxQuestions} relevant questions that could be asked about this content:\n\nTitle: {$note->title}\n\nContent:\n{$content}\n\nSuggest questions (one per line):";

            $response = $this->aiService->callOllama($prompt, [
                'temperature' => 0.7,
                'num_predict' => 300,
            ]);

            if ($response && isset($response['response'])) {
                $questions = $this->extractQuestions($response['response']);
                return array_slice($questions, 0, $maxQuestions);
            }

            return [];
        } catch (Exception $e) {
            Log::error('Question suggestion failed', [
                'error' => $e->getMessage(),
                'note_id' => $note->id,
            ]);

            return [];
        }
    }

    /**
     * Extract questions from AI response.
     */
    protected function extractQuestions(string $response): array
    {
        $response = trim($response);
        
        // Split by newline or number/bullet patterns
        $lines = preg_split('/\n|\r\n?/', $response);
        
        $questions = [];
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Remove numbering/bullets
            $line = preg_replace('/^[\d\.\-\•\*]\s*/', '', $line);
            
            // Check if it looks like a question
            if (preg_match('/\?$/', $line) || preg_match('/^(apa|bagaimana|mengapa|kapan|dimana|siapa|what|how|why|when|where|who)/i', $line)) {
                if (strlen($line) > 10 && strlen($line) < 200) {
                    $questions[] = $line;
                }
            }
        }

        return array_filter($questions);
    }
}

