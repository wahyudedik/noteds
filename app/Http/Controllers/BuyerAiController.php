<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\PurchasedNote;
use App\Models\AiAnalysis;
use App\Services\AiService;
use App\Services\ContentExtractorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BuyerAiController extends Controller
{
    public function __construct(
        protected AiService $aiService,
        protected ContentExtractorService $contentExtractor
    ) {}

    /**
     * Analyze a purchased note (Premium feature only).
     * Returns summary, key points, insights, topics, difficulty, and estimated time.
     */
    public function analyzePurchasedNote(Request $request, Note $note): JsonResponse
    {
        $user = auth()->user();

        // Check premium subscription
        if (!$user->hasPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur AI ini memerlukan subscription premium. Silakan upgrade untuk menggunakan fitur ini.',
                'requires_premium' => true,
            ], 403);
        }

        // Check if user has purchased this note
        $purchasedNote = auth()->user()->getPurchasedNote($note->id);
        if (!$purchasedNote && $note->price > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus membeli note ini terlebih dahulu untuk menggunakan fitur AI analyzer.',
            ], 403);
        }

        // Check if analysis already exists
        $existingAnalysis = AiAnalysis::where('user_id', $user->id)
            ->where('note_id', $note->id)
            ->where('analysis_type', 'analyzer')
            ->first();

        if ($existingAnalysis) {
            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $existingAnalysis->summary,
                    'key_points' => $existingAnalysis->key_points ?? [],
                    'insights' => $existingAnalysis->insights ?? [],
                    'topics' => $existingAnalysis->topics ?? [],
                    'difficulty_level' => $existingAnalysis->difficulty_level,
                    'estimated_time_minutes' => $existingAnalysis->estimated_time_minutes,
                ],
                'cached' => true,
            ]);
        }

        // Check if Ollama is available
        if (!$this->aiService->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'AI service is currently unavailable. Please try again later.',
            ], 503);
        }

        try {
            $content = strip_tags($note->content);
            $totalLines = count(preg_split('/\r\n|\r|\n/', $content));

            // Generate summary
            $summary = $this->aiService->generateSummary($content, 500);

            // Extract key points using AI
            $keyPointsPrompt = "Dari konten berikut, ekstrak 5-10 poin penting:\n\n" . substr($content, 0, 3000);
            $keyPointsResponse = $this->aiService->callOllama($keyPointsPrompt, ['num_predict' => 200]);
            $keyPoints = $this->parseKeyPoints($keyPointsResponse['response'] ?? '');

            // Generate insights
            $insightsPrompt = "Dari konten berikut, berikan 3-5 insight utama:\n\n" . substr($content, 0, 3000);
            $insightsResponse = $this->aiService->callOllama($insightsPrompt, ['num_predict' => 200]);
            $insights = $this->parseInsights($insightsResponse['response'] ?? '');

            // Extract topics
            $topics = $this->aiService->suggestTags($content, 5);

            // Estimate difficulty and reading time
            $difficultyLevel = $this->estimateDifficulty($content);
            $estimatedTimeMinutes = $this->estimateReadingTime($content);

            // Save analysis
            $analysis = AiAnalysis::create([
                'user_id' => $user->id,
                'note_id' => $note->id,
                'analysis_type' => 'analyzer',
                'summary' => $summary,
                'key_points' => $keyPoints,
                'insights' => $insights,
                'topics' => $topics,
                'difficulty_level' => $difficultyLevel,
                'estimated_time_minutes' => $estimatedTimeMinutes,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'key_points' => $keyPoints,
                    'insights' => $insights,
                    'topics' => $topics,
                    'difficulty_level' => $difficultyLevel,
                    'estimated_time_minutes' => $estimatedTimeMinutes,
                ],
            ]);
        } catch (\Exception $e) {
            logger()->error('AI Note Analyzer error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while analyzing the note.',
            ], 500);
        }
    }

    /**
     * Ask questions about purchased notes (Premium feature only).
     */
    public function askPurchasedNote(Request $request): JsonResponse
    {
        $user = auth()->user();

        // Check premium subscription
        if (!$user->hasPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur AI ini memerlukan subscription premium. Silakan upgrade untuk menggunakan fitur ini.',
                'requires_premium' => true,
            ], 403);
        }

        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'note_ids' => 'required|array|min:1',
            'note_ids.*' => 'exists:notes,id',
        ]);

        $question = $validated['question'];
        $noteIds = $validated['note_ids'];

        // Verify user has purchased all notes
        foreach ($noteIds as $noteId) {
            $note = Note::findOrFail($noteId);
            if ($note->price > 0 && !$user->hasPurchasedNote($noteId)) {
                return response()->json([
                    'success' => false,
                    'message' => "Anda harus membeli note dengan ID {$noteId} terlebih dahulu.",
                ], 403);
            }
        }

        // Get notes content
        $notes = Note::whereIn('id', $noteIds)
            ->get(['id', 'title', 'content']);

        if ($notes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No notes found.',
            ], 404);
        }

        // Check if Ollama is available
        if (!$this->aiService->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'AI service is currently unavailable. Please try again later.',
            ], 503);
        }

        try {
            // Prepare note contents for AI
            $noteContents = $notes->map(fn($note) => "Title: {$note->title}\nContent: " . strip_tags($note->content))->toArray();
            $noteIdsArray = $notes->pluck('id')->toArray();

            // Get answer from AI
            $result = $this->aiService->answerQuestion($question, $noteContents, $noteIdsArray);

            if ($result && isset($result['answer'])) {
                $referencedNotes = [];
                if (!empty($result['referenced_note_ids'])) {
                    $referencedNotes = $notes->whereIn('id', $result['referenced_note_ids'])
                        ->map(fn($note) => [
                            'id' => $note->id,
                            'title' => $note->title,
                        ])->values()->toArray();
                }

                return response()->json([
                    'success' => true,
                    'answer' => $result['answer'],
                    'referenced_notes' => $referencedNotes,
                    'notes_searched' => $notes->count(),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate answer. Please try again.',
            ], 500);
        } catch (\Exception $e) {
            logger()->error('AI Q&A error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your question.',
            ], 500);
        }
    }

    /**
     * Generate study materials (flashcards, quiz, study guide) from purchased note.
     */
    public function generateStudyMaterials(Request $request, Note $note): JsonResponse
    {
        $user = auth()->user();

        // Check premium subscription
        if (!$user->hasPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur AI ini memerlukan subscription premium. Silakan upgrade untuk menggunakan fitur ini.',
                'requires_premium' => true,
            ], 403);
        }

        $validated = $request->validate([
            'type' => 'required|in:flashcards,quiz,study_guide,mind_map',
        ]);

        // Check if user has purchased this note
        $purchasedNote = auth()->user()->getPurchasedNote($note->id);
        if (!$purchasedNote && $note->price > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus membeli note ini terlebih dahulu untuk menggunakan fitur study materials.',
            ], 403);
        }

        // Check if Ollama is available
        if (!$this->aiService->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'AI service is currently unavailable. Please try again later.',
            ], 503);
        }

        try {
            $content = strip_tags($note->content);
            $type = $validated['type'];
            $studyMaterial = $this->generateStudyMaterialContent($content, $type);

            // Save study material
            \App\Models\StudyMaterial::create([
                'user_id' => $user->id,
                'note_id' => $note->id,
                'type' => $type,
                'title' => $note->title . ' - ' . ucfirst(str_replace('_', ' ', $type)),
                'content' => $studyMaterial['content'],
                'item_count' => $studyMaterial['item_count'],
            ]);

            return response()->json([
                'success' => true,
                'data' => $studyMaterial,
            ]);
        } catch (\Exception $e) {
            logger()->error('Study Material Generation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating study materials.',
            ], 500);
        }
    }

    /**
     * Compare multiple purchased notes.
     */
    public function compareNotes(Request $request): JsonResponse
    {
        $user = auth()->user();

        // Check premium subscription
        if (!$user->hasPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur AI ini memerlukan subscription premium. Silakan upgrade untuk menggunakan fitur ini.',
                'requires_premium' => true,
            ], 403);
        }

        $validated = $request->validate([
            'note_ids' => 'required|array|min:2|max:5',
            'note_ids.*' => 'exists:notes,id',
        ]);

        $noteIds = $validated['note_ids'];

        // Verify user has purchased all notes
        foreach ($noteIds as $noteId) {
            $note = Note::findOrFail($noteId);
            if ($note->price > 0 && !$user->hasPurchasedNote($noteId)) {
                return response()->json([
                    'success' => false,
                    'message' => "Anda harus membeli note dengan ID {$noteId} terlebih dahulu.",
                ], 403);
            }
        }

        // Get notes
        $notes = Note::whereIn('id', $noteIds)
            ->get(['id', 'title', 'content']);

        // Check if Ollama is available
        if (!$this->aiService->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'AI service is currently unavailable. Please try again later.',
            ], 503);
        }

        try {
            $comparison = $this->compareNotesContent($notes);

            return response()->json([
                'success' => true,
                'data' => $comparison,
            ]);
        } catch (\Exception $e) {
            logger()->error('Note Comparison error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while comparing notes.',
            ], 500);
        }
    }

    /**
     * Get AI recommendations based on purchased notes.
     */
    public function getRecommendations(Request $request): JsonResponse
    {
        $user = auth()->user();

        // Check premium subscription
        if (!$user->hasPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur AI ini memerlukan subscription premium. Silakan upgrade untuk menggunakan fitur ini.',
                'requires_premium' => true,
            ], 403);
        }

        // Get user's purchased notes
        $purchasedNotes = $user->purchasedNotes()
            ->with('note.tags')
            ->latest('purchased_at')
            ->limit(20)
            ->get();

        if ($purchasedNotes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada note yang dibeli. Beli beberapa note terlebih dahulu untuk mendapatkan rekomendasi.',
                'recommendations' => [],
            ]);
        }

        // Get tags from purchased notes
        $tags = [];
        foreach ($purchasedNotes as $purchased) {
            foreach ($purchased->note->tags as $tag) {
                $tags[$tag->id] = $tag->name;
            }
        }

        // Get similar notes based on tags
        $purchasedNoteIds = $purchasedNotes->pluck('note_id')->toArray();
        $recommendedNotes = Note::publicOnly()
            ->where('status', 'active')
            ->whereNotIn('id', $purchasedNoteIds)
            ->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('tags.id', array_keys($tags));
            })
            ->with(['tags', 'user', 'reviews'])
            ->inRandomOrder()
            ->limit(10)
            ->get()
            ->map(function ($note) use ($tags) {
                $matchingTags = $note->tags->filter(fn($tag) => isset($tags[$tag->id]))->pluck('name')->toArray();
                return [
                    'id' => $note->id,
                    'title' => $note->title,
                    'summary' => $note->summary,
                    'price' => $note->price,
                    'discount_price' => $note->discount_price,
                    'average_rating' => $note->average_rating,
                    'user' => [
                        'id' => $note->user->id,
                        'name' => $note->user->name,
                    ],
                    'matching_tags' => $matchingTags,
                    'reason' => 'Similar tags: ' . implode(', ', $matchingTags),
                ];
            });

        return response()->json([
            'success' => true,
            'recommendations' => $recommendedNotes,
        ]);
    }

    // Helper methods
    private function parseKeyPoints(string $response): array
    {
        // Parse AI response into array of key points
        $lines = explode("\n", $response);
        $keyPoints = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            // Remove numbering (1., 2., - , etc)
            $line = preg_replace('/^[\d\-\•\*]\s*/', '', $line);
            if (strlen($line) > 10) {
                $keyPoints[] = $line;
            }
        }
        return array_slice($keyPoints, 0, 10);
    }

    private function parseInsights(string $response): array
    {
        return $this->parseKeyPoints($response); // Same parsing logic
    }

    private function estimateDifficulty(string $content): string
    {
        $length = strlen($content);
        $wordCount = str_word_count($content);

        // Simple heuristic: based on length and complexity
        if ($wordCount < 500) {
            return 'beginner';
        } elseif ($wordCount < 2000) {
            return 'intermediate';
        } else {
            return 'advanced';
        }
    }

    private function estimateReadingTime(string $content): int
    {
        // Average reading speed: 200-250 words per minute
        $wordCount = str_word_count($content);
        $minutes = (int) ceil($wordCount / 200);
        return max(1, $minutes); // Minimum 1 minute
    }

    private function generateStudyMaterialContent(string $content, string $type): array
    {
        $prompt = match ($type) {
            'flashcards' => "Buatkan flashcards dari konten berikut. Format: pertanyaan di baris pertama, jawaban di baris kedua, dipisahkan dengan '|'. Minimal 10 flashcards:\n\n" . substr($content, 0, 3000),
            'quiz' => "Buatkan quiz dengan pertanyaan pilihan ganda dari konten berikut. Format: pertanyaan, lalu opsi A, B, C, D, lalu jawaban benar. Minimal 10 pertanyaan:\n\n" . substr($content, 0, 3000),
            'study_guide' => "Buatkan study guide terstruktur dari konten berikut dengan outline, poin-poin penting, dan ringkasan:\n\n" . substr($content, 0, 3000),
            'mind_map' => "Buatkan mind map dalam format teks dari konten berikut dengan topik utama dan sub-topik:\n\n" . substr($content, 0, 3000),
        };

        $response = $this->aiService->callOllama($prompt, ['num_predict' => 1000]);
        $parsed = $this->parseStudyMaterial($response['response'] ?? '', $type);

        return [
            'content' => $parsed,
            'item_count' => count($parsed),
        ];
    }

    private function parseStudyMaterial(string $response, string $type): array
    {
        // Basic parsing - can be enhanced
        $lines = explode("\n", $response);
        $items = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strlen($line) < 10) continue;

            if ($type === 'flashcards') {
                if (strpos($line, '|') !== false) {
                    [$question, $answer] = explode('|', $line, 2);
                    $items[] = ['question' => trim($question), 'answer' => trim($answer)];
                }
            } elseif ($type === 'quiz') {
                // Parse quiz format
                if (preg_match('/^[A-D]\)/', $line)) {
                    $items[] = ['option' => $line];
                } elseif (!empty($line) && !preg_match('/^Jawaban|Answer/', $line)) {
                    $items[] = ['question' => $line];
                }
            } else {
                $items[] = ['content' => $line];
            }
        }

        return array_slice($items, 0, 20); // Limit items
    }

    private function compareNotesContent($notes): array
    {
        $prompt = "Bandingkan konten dari " . $notes->count() . " note berikut:\n\n";
        foreach ($notes as $index => $note) {
            $prompt .= "Note " . ($index + 1) . " - {$note->title}:\n" . substr(strip_tags($note->content), 0, 1000) . "\n\n";
        }
        $prompt .= "Berikan analisis: kesamaan, perbedaan, dan insight utama.";

        $response = $this->aiService->callOllama($prompt, ['num_predict' => 500]);

        return [
            'comparison' => $response['response'] ?? 'Tidak dapat membandingkan note.',
            'notes_compared' => $notes->map(fn($n) => ['id' => $n->id, 'title' => $n->title])->toArray(),
        ];
    }

    /**
     * Extract content from note attachments (PDF text, Image OCR, Tables)
     * Premium feature only.
     */
    public function extractContent(Request $request, Note $note): JsonResponse
    {
        $user = auth()->user();

        // Check premium subscription
        if (!$user->hasPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur AI Content Extractor memerlukan subscription premium.',
                'requires_premium' => true,
            ], 403);
        }

        // Check if user has purchased this note
        $purchasedNote = $user->getPurchasedNote($note->id);
        if (!$purchasedNote && $note->price > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus membeli note ini terlebih dahulu untuk menggunakan fitur AI Content Extractor.',
            ], 403);
        }

        $validated = $request->validate([
            'filename' => ['required', 'string'],
            'extract_type' => ['nullable', 'in:text,ocr,tables,all'],
        ]);

        $filename = $validated['filename'];
        $extractType = $validated['extract_type'] ?? 'all';

        // Find the file in attachments
        $attachments = $note->attachments ?? [];
        $filePath = null;
        $mimeType = null;

        foreach ($attachments as $attachment) {
            $attachmentFilename = is_array($attachment) 
                ? ($attachment['filename'] ?? null) 
                : basename($attachment);

            if ($attachmentFilename === $filename) {
                $filePath = is_array($attachment) 
                    ? ($attachment['path'] ?? null) 
                    : $attachment;
                $mimeType = is_array($attachment) 
                    ? ($attachment['mime'] ?? null) 
                    : null;
                break;
            }
        }

        if (!$filePath || !Storage::disk('private')->exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found in note attachments.',
            ], 404);
        }

        // Detect MIME type if not provided
        if (!$mimeType) {
            $mimeType = Storage::disk('private')->mimeType($filePath) ?? 'application/octet-stream';
        }

        try {
            $result = [
                'filename' => $filename,
                'file_path' => $filePath,
                'mime_type' => $mimeType,
            ];

            // Extract based on type
            if ($extractType === 'all' || $extractType === 'text') {
                if ($mimeType === 'application/pdf') {
                    $pdfResult = $this->contentExtractor->extractPdfText($filePath);
                    if ($pdfResult) {
                        $result['pdf_text'] = $pdfResult;
                    }
                }
            }

            if ($extractType === 'all' || $extractType === 'ocr') {
                if (str_starts_with($mimeType, 'image/')) {
                    $ocrResult = $this->contentExtractor->extractImageText($filePath);
                    if ($ocrResult) {
                        $result['ocr_text'] = $ocrResult;
                    }
                }
            }

            if ($extractType === 'all' || $extractType === 'tables') {
                $fileType = $mimeType === 'application/pdf' ? 'pdf' : 'image';
                $tablesResult = $this->contentExtractor->extractTables($filePath, $fileType);
                if ($tablesResult) {
                    $result['tables'] = $tablesResult;
                }
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Content extraction failed', [
                'error' => $e->getMessage(),
                'note_id' => $note->id,
                'filename' => $filename,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengekstrak konten. Silakan coba lagi.',
            ], 500);
        }
    }
}
