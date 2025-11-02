<?php

namespace App\Http\Controllers;

use App\Services\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function __construct(
        protected AiService $aiService
    ) {}

    /**
     * Generate summary and suggest tags for a note.
     * Basic AI feature - available to all authenticated users.
     */
    public function analyze(Request $request): JsonResponse
    {
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

            // Suggest tags
            $suggestedTags = $this->aiService->suggestTags($content, 5);

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'tags' => $suggestedTags,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request.',
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
}
