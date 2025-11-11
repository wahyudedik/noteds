<?php

namespace App\Http\Controllers;

use App\Services\AiService;
use App\Services\AiUsageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function __construct(
        protected AiService $aiService,
        protected AiUsageService $aiUsageService
    ) {}

    /**
     * Generate summary and suggest tags for a note.
     * Premium feature only - requires subscription.
     */
    public function analyze(Request $request): JsonResponse
    {
        // Check premium subscription
        if (!auth()->user()->hasPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur AI ini memerlukan subscription premium. Silakan upgrade untuk menggunakan fitur ini.',
                'requires_premium' => true,
            ], 403);
        }

        $request->validate([
            'content' => 'required|string|max:10000',
        ]);

        try {
            $content = $request->input('content');

            // Check if Ollama is available
            if (! $this->aiService->isAvailable()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI service is currently unavailable. Please try again later.',
                ], 503);
            }

            // Generate summary
            $summary = $this->aiService->generateSummary($content, 200);
            if ($summary === null || empty(trim($summary))) {
                $summary = 'Tidak dapat menghasilkan ringkasan.';
            }

            // Suggest tags
            $suggestedTags = $this->aiService->suggestTags($content, 5);
            if (empty($suggestedTags)) {
                $suggestedTags = [];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'tags' => $suggestedTags,
                ],
            ]);
        } catch (\Exception $e) {
            logger()->error('AI analyze error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request. Please try again later.',
            ], 500);
        }
    }

    /**
     * Advanced AI Q&A - Premium feature only.
     * Answer questions based on user's notes using semantic search.
     * 
     * Example: "Apa yang kubicarakan dengan Rina minggu lalu?"
     */
    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'note_ids' => 'nullable|array',
            'note_ids.*' => 'exists:notes,id',
        ]);

        // This is a premium feature - middleware ensures user has premium
        $user = $request->user();
        $question = $validated['question'];

        // Get notes to search through
        $notesQuery = $user->notes();
        
        // If specific note IDs provided, filter by them
        if (!empty($validated['note_ids'])) {
            $notesQuery->whereIn('id', $validated['note_ids']);
        }

        // Limit to recent notes for performance (last 100 notes)
        $notes = $notesQuery->latest()->limit(100)->get(['id', 'title', 'content']);

        if ($notes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No notes found to search through. Please create some notes first.',
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
            // Prepare note contents for AI with note IDs
            $noteContents = $notes->map(fn($note) => "Title: {$note->title}\nContent: " . strip_tags($note->content))->toArray();
            $noteIds = $notes->pluck('id')->toArray();
            // Create mapping: note number (1-indexed) => note ID
            $noteNumberToId = [];
            foreach ($notes as $index => $note) {
                $noteNumberToId[$index + 1] = $note->id;
            }

            // Get answer from AI with note references
            $result = $this->aiService->answerQuestion($question, $noteContents, $noteIds);

            if ($result && isset($result['answer'])) {
                // Get referenced note details with their original numbers
                $referencedNotes = [];
                if (!empty($result['referenced_note_ids'])) {
                    // Get notes with their order preserved
                    $referencedNotesData = $notes->whereIn('id', $result['referenced_note_ids']);
                    
                    // Map to include note number from answer
                    $referencedNotes = [];
                    foreach ($result['referenced_note_ids'] as $noteId) {
                        $note = $notes->firstWhere('id', $noteId);
                        if ($note) {
                            // Find the note number (1-indexed) from our mapping
                            $noteNumber = array_search($noteId, $noteNumberToId);
                            $referencedNotes[] = [
                                'id' => $note->id,
                                'title' => $note->title,
                                'number' => $noteNumber, // Include the note number from answer
                            ];
                        }
                    }
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
     * Semantic search - Premium feature only.
     * Search notes using AI-based semantic understanding.
     */
    public function semanticSearch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'required|string|max:500',
        ]);

        // This is a premium feature - middleware ensures user has premium
        $user = $request->user();
        $query = $validated['query']; // Get validated query as string

        // Get user's notes
        $notes = $user->notes()
            ->latest()
            ->limit(100) // Limit for performance
            ->get(['id', 'title', 'content']);

        if ($notes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No notes found to search.',
                'results' => [],
            ], 404);
        }

        // Check if Ollama is available
        if (!$this->aiService->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'AI service is currently unavailable. Please try again later.',
                'results' => [],
            ], 503);
        }

        try {
            // Prepare notes for semantic search
            $notesData = $notes->map(fn($note) => [
                'id' => $note->id,
                'title' => $note->title,
                'content' => strip_tags($note->content),
            ])->toArray();

            // Get relevant note IDs using semantic search
            $relevantIds = $this->aiService->semanticSearch($query, $notesData);

            // Return notes in order of relevance
            $relevantNotes = collect($notesData)
                ->filter(fn($note) => in_array($note['id'], $relevantIds))
                ->sortBy(fn($note) => array_search($note['id'], $relevantIds))
                ->values()
                ->map(fn($note) => [
                    'id' => $note['id'],
                    'title' => $note['title'],
                    'preview' => \Illuminate\Support\Str::limit($note['content'], 200),
                ]);

            return response()->json([
                'success' => true,
                'query' => $query,
                'results' => $relevantNotes,
                'total' => $relevantNotes->count(),
            ]);
        } catch (\Exception $e) {
            logger()->error('Semantic search error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during semantic search.',
                'results' => [],
            ], 500);
        }
    }

    /**
     * Context linking - Detect relationships between notes.
     * Premium feature only.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function contextLinks(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'note_id' => 'nullable|exists:notes,id', // Optional: focus on specific note
            'limit' => 'nullable|integer|min:1|max:50', // Limit number of notes to analyze
        ]);

        // This is a premium feature - middleware ensures user has premium
        $user = $request->user();
        $focusNoteId = $validated['note_id'] ?? null;
        $limit = $validated['limit'] ?? 30;

        // Get user's notes
        $notesQuery = $user->notes();
        
        // If focus note ID provided, ensure it's included
        if ($focusNoteId) {
            $notesQuery->where('id', $focusNoteId)
                ->orWhere(function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
        }

        // Get notes for analysis
        $notes = $notesQuery
            ->latest()
            ->limit($limit)
            ->get(['id', 'title', 'content']);

        if ($notes->count() < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Need at least 2 notes to detect relationships.',
                'links' => [],
            ], 400);
        }

        // Check if Ollama is available
        if (!$this->aiService->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'AI service is currently unavailable. Please try again later.',
                'links' => [],
            ], 503);
        }

        try {
            // Prepare notes data
            $notesData = $notes->map(fn($note) => [
                'id' => $note->id,
                'title' => $note->title,
                'content' => strip_tags($note->content),
            ])->toArray();

            // Detect context links
            $links = $this->aiService->detectContextLinks($notesData, $focusNoteId);

            // Enrich links with note titles
            $enrichedLinks = [];
            foreach ($links as $link) {
                $note1 = $notes->firstWhere('id', $link['note_id_1']);
                $note2 = $notes->firstWhere('id', $link['note_id_2']);
                
                if ($note1 && $note2) {
                    $enrichedLinks[] = [
                        'note_1' => [
                            'id' => $note1->id,
                            'title' => $note1->title,
                        ],
                        'note_2' => [
                            'id' => $note2->id,
                            'title' => $note2->title,
                        ],
                        'relationship' => $link['relationship'],
                        'strength' => $link['strength'],
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'links' => $enrichedLinks,
                'total' => count($enrichedLinks),
                'notes_analyzed' => $notes->count(),
            ]);
        } catch (\Exception $e) {
            logger()->error('Context linking error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while detecting context links.',
                'links' => [],
            ], 500);
        }
    }

    /**
     * Check if AI service is available.
     */
    public function status(): JsonResponse
    {
        $available = $this->aiService->isAvailable();

        return response()->json([
            'available' => $available,
            'service' => 'Ollama',
        ]);
    }

    /**
     * Generate content from a prompt (like LLM).
     * Premium feature only - requires subscription.
     */
    public function generateContent(Request $request): JsonResponse
    {
        // Check premium subscription
        if (!auth()->user()->hasPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur AI ini memerlukan subscription premium. Silakan upgrade untuk menggunakan fitur ini.',
                'requires_premium' => true,
            ], 403);
        }

        $request->validate([
            'prompt' => 'required|string|max:1000',
            'max_length' => 'nullable|integer|min:100|max:5000',
        ]);

        try {
            $prompt = $request->input('prompt');
            $maxLength = $request->input('max_length', 2000);

            // Check if Ollama is available
            if (!$this->aiService->isAvailable()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI service is currently unavailable. Please try again later.',
                ], 503);
            }

            // Generate content
            $content = $this->aiService->generateContent($prompt, $maxLength);

            if ($content && !empty(trim($content))) {
                return response()->json([
                    'success' => true,
                    'content' => $content,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghasilkan konten. Silakan coba lagi dengan prompt yang berbeda.',
            ], 500);
        } catch (\Exception $e) {
            logger()->error('AI Content generation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating content.',
            ], 500);
        }
    }

    /**
     * Search for images based on query.
     * Premium feature only - requires subscription.
     */
    public function searchImages(Request $request): JsonResponse
    {
        // Check premium subscription
        if (!auth()->user()->hasPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur AI ini memerlukan subscription premium. Silakan upgrade untuk menggunakan fitur ini.',
                'requires_premium' => true,
            ], 403);
        }

        $request->validate([
            'query' => 'required|string|max:200',
            'limit' => 'nullable|integer|min:1|max:30',
        ]);

        $user = $request->user();
        $decision = $this->aiUsageService->checkAvailability($user, AiUsageService::FEATURE_IMAGE_SEARCH);

        if (! $decision['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $decision['message'] ?? 'Saldo wallet kamu tidak mencukupi untuk menggunakan fitur AI ini.',
                'requires_payment' => true,
                'amount' => $decision['amount'],
                'currency' => $decision['currency'],
                'wallet_balance' => $decision['wallet_balance'] ?? null,
                'usage_summary' => $decision['usage_summary'],
            ], 402);
        }

        try {
            $query = $request->input('query');
            $limit = $request->input('limit', 10);

            // Search images
            $images = $this->aiService->searchImages($query, $limit);

            $billing = $this->aiUsageService->recordUsage(
                $user,
                AiUsageService::FEATURE_IMAGE_SEARCH,
                $decision,
                true,
                [
                    'query' => $query,
                    'limit' => $limit,
                    'total' => count($images),
                ]
            );

            $usageSummary = $this->aiUsageService->getUsageSummary($user);

            return response()->json([
                'success' => true,
                'images' => $images,
                'total' => count($images),
                'billing' => $billing,
                'usage_summary' => $usageSummary,
                'charged' => $billing['charged'],
            ]);
        } catch (\Exception $e) {
            logger()->error('Image search error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching for images.',
                'images' => [],
                'usage_summary' => $decision['usage_summary'] ?? null,
            ], 500);
        }
    }

    /**
     * Generate image from text prompt.
     * Premium feature only - requires subscription.
     */
    public function generateImage(Request $request): JsonResponse
    {
        // Check premium subscription
        if (!auth()->user()->hasPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur AI ini memerlukan subscription premium. Silakan upgrade untuk menggunakan fitur ini.',
                'requires_premium' => true,
            ], 403);
        }

        $request->validate([
            'prompt' => 'required|string|max:500',
            'size' => 'nullable|string|in:512x512,1024x1024,1024x1792,1792x1024',
            'style' => 'nullable|string|in:vivid,natural',
        ]);

        $user = $request->user();
        $decision = $this->aiUsageService->checkAvailability($user, AiUsageService::FEATURE_IMAGE_GENERATE);

        if (! $decision['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $decision['message'] ?? 'Saldo wallet kamu tidak mencukupi untuk menggunakan fitur AI ini.',
                'requires_payment' => true,
                'amount' => $decision['amount'],
                'currency' => $decision['currency'],
                'wallet_balance' => $decision['wallet_balance'] ?? null,
                'usage_summary' => $decision['usage_summary'],
            ], 402);
        }

        try {
            $prompt = $request->input('prompt');
            $options = [
                'size' => $request->input('size', '1024x1024'),
                'style' => $request->input('style', 'vivid'),
            ];

            // Generate image
            $result = $this->aiService->generateImage($prompt, $options);

            if ($result) {
                $billing = $this->aiUsageService->recordUsage(
                    $user,
                    AiUsageService::FEATURE_IMAGE_GENERATE,
                    $decision,
                    true,
                    [
                        'prompt_length' => mb_strlen($prompt),
                        'size' => $options['size'],
                        'style' => $options['style'],
                    ]
                );

                $usageSummary = $this->aiUsageService->getUsageSummary($user);

                return response()->json([
                    'success' => true,
                    'image' => $result,
                    'billing' => $billing,
                    'usage_summary' => $usageSummary,
                    'charged' => $billing['charged'],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate image. Please ensure image generation API is configured.',
                'usage_summary' => $decision['usage_summary'],
            ], 500);
        } catch (\Exception $e) {
            logger()->error('Image generation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating image.',
                'usage_summary' => $decision['usage_summary'],
            ], 500);
        }
    }

    /**
     * Generate video from text prompt.
     * Premium feature only - requires subscription.
     */
    public function generateVideo(Request $request): JsonResponse
    {
        // Check premium subscription
        if (!auth()->user()->hasPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur AI ini memerlukan subscription premium. Silakan upgrade untuk menggunakan fitur ini.',
                'requires_premium' => true,
            ], 403);
        }

        $request->validate([
            'prompt' => 'required|string|max:500',
            'duration' => 'nullable|integer|min:1|max:10',
            'ratio' => 'nullable|string|in:16:9,9:16,1:1',
        ]);

        $user = $request->user();
        $decision = $this->aiUsageService->checkAvailability($user, AiUsageService::FEATURE_VIDEO_GENERATE);

        if (! $decision['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $decision['message'] ?? 'Saldo wallet kamu tidak mencukupi untuk menggunakan fitur AI ini.',
                'requires_payment' => true,
                'amount' => $decision['amount'],
                'currency' => $decision['currency'],
                'wallet_balance' => $decision['wallet_balance'] ?? null,
                'usage_summary' => $decision['usage_summary'],
            ], 402);
        }

        try {
            $prompt = $request->input('prompt');
            $options = [
                'duration' => $request->input('duration', 5),
                'ratio' => $request->input('ratio', '16:9'),
            ];

            // Generate video
            $result = $this->aiService->generateVideo($prompt, $options);

            if ($result) {
                $billing = $this->aiUsageService->recordUsage(
                    $user,
                    AiUsageService::FEATURE_VIDEO_GENERATE,
                    $decision,
                    true,
                    [
                        'prompt_length' => mb_strlen($prompt),
                        'duration' => $options['duration'],
                        'ratio' => $options['ratio'],
                        'result_keys' => array_keys((array) $result),
                    ]
                );

                $usageSummary = $this->aiUsageService->getUsageSummary($user);

                return response()->json([
                    'success' => true,
                    'video' => $result,
                    'billing' => $billing,
                    'usage_summary' => $usageSummary,
                    'charged' => $billing['charged'],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate video. Please ensure video generation API is configured.',
                'usage_summary' => $decision['usage_summary'],
            ], 500);
        } catch (\Exception $e) {
            logger()->error('Video generation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating video.',
                'usage_summary' => $decision['usage_summary'],
            ], 500);
        }
    }

    /**
     * Edit video with specified edits.
     * Premium feature only - requires subscription.
     */
    public function editVideo(Request $request): JsonResponse
    {
        // Check premium subscription
        if (!auth()->user()->hasPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur AI ini memerlukan subscription premium. Silakan upgrade untuk menggunakan fitur ini.',
                'requires_premium' => true,
            ], 403);
        }

        $request->validate([
            'video_url' => 'required|url',
            'edits' => 'required|array',
            'edits.trim' => 'nullable|array',
            'edits.effects' => 'nullable|array',
        ]);

        try {
            $videoUrl = $request->input('video_url');
            $edits = $request->input('edits');

            // Edit video
            $result = $this->aiService->editVideo($videoUrl, $edits);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'video' => $result,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to edit video.',
            ], 500);
        } catch (\Exception $e) {
            logger()->error('Video editing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while editing video.',
            ], 500);
        }
    }

    /**
     * Generate content ideas based on topic.
     * Premium feature only - requires subscription.
     */
    public function generateIdeas(Request $request): JsonResponse
    {
        // Check premium subscription
        if (!auth()->user()->hasPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur AI ini memerlukan subscription premium. Silakan upgrade untuk menggunakan fitur ini.',
                'requires_premium' => true,
            ], 403);
        }

        $request->validate([
            'topic' => 'required|string|max:200',
            'count' => 'nullable|integer|min:1|max:10',
        ]);

        try {
            $topic = $request->input('topic');
            $count = $request->input('count', 5);

            // Check if Ollama is available
            if (!$this->aiService->isAvailable()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI service is currently unavailable. Please try again later.',
                ], 503);
            }

            // Generate ideas
            $ideas = $this->aiService->generateIdeas($topic, $count);

            if (!empty($ideas)) {
                return response()->json([
                    'success' => true,
                    'ideas' => $ideas,
                    'count' => count($ideas),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate ideas. Please try again.',
            ], 500);
        } catch (\Exception $e) {
            logger()->error('Idea generation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating ideas.',
            ], 500);
        }
    }
}
