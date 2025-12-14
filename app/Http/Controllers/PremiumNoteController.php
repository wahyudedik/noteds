<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Services\SmartSearchService;
use App\Services\NoteQAService;
use App\Services\AiInsightService;
use App\Services\ContextLinkingService;
use App\Services\EmbeddingService;
use App\Services\AiMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PremiumNoteController extends Controller
{
    public function __construct(
        protected SmartSearchService $searchService,
        protected NoteQAService $qaService,
        protected AiInsightService $insightService,
        protected ContextLinkingService $contextLinkingService,
        protected EmbeddingService $embeddingService,
        protected AiMonitoringService $monitoringService
    ) {
        $this->middleware('auth');
        $this->middleware('verified');
    }

    /**
     * Smart search page.
     */
    public function search(Request $request): View
    {
        $user = auth()->user();
        
        if (!$user->hasPremium()) {
            abort(403, 'Fitur ini memerlukan subscription premium.');
        }

        $query = $request->get('q', '');
        $notes = collect();
        $suggestions = [];

        if (!empty($query)) {
            $filters = [
                'workspace_id' => $request->get('workspace_id'),
                'folder_id' => $request->get('folder_id'),
                'tags' => $request->get('tags', []),
                'status' => $request->get('status'),
                'per_page' => 15,
            ];

            $notes = $this->searchService->search($query, $user, $filters);
            $suggestions = $this->searchService->getSuggestions($query, $user);
        }

        return view('40-shared/premium/search', compact('notes', 'query', 'suggestions'));
    }

    /**
     * Smart search API endpoint.
     */
    public function searchApi(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user->hasPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur ini memerlukan subscription premium.',
            ], 403);
        }

        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Query tidak boleh kosong.',
            ], 422);
        }

        $filters = [
            'workspace_id' => $request->get('workspace_id'),
            'folder_id' => $request->get('folder_id'),
            'tags' => $request->get('tags', []),
            'status' => $request->get('status'),
            'per_page' => $request->get('per_page', 15),
        ];

        $notes = $this->searchService->search($query, $user, $filters);
        $suggestions = $this->searchService->getSuggestions($query, $user);

        return response()->json([
            'success' => true,
            'notes' => $notes->items(),
            'pagination' => [
                'current_page' => $notes->currentPage(),
                'last_page' => $notes->lastPage(),
                'per_page' => $notes->perPage(),
                'total' => $notes->total(),
            ],
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Ask question about a note.
     */
    public function askQuestion(Request $request, Note $note): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user->hasPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur ini memerlukan subscription premium.',
            ], 403);
        }

        $request->validate([
            'question' => 'required|string|max:500',
        ]);

        $result = $this->qaService->answerQuestion($note, $request->question, $user);

        return response()->json($result);
    }

    /**
     * Get suggested questions for a note.
     */
    public function getSuggestedQuestions(Note $note): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user->hasPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur ini memerlukan subscription premium.',
            ], 403);
        }

        // Check access
        if ($note->user_id !== $user->id && !$note->is_public) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke catatan ini.',
            ], 403);
        }

        $questions = $this->qaService->suggestQuestions($note);

        return response()->json([
            'success' => true,
            'questions' => $questions,
        ]);
    }

    /**
     * Ask question about multiple notes.
     */
    public function askQuestionAboutNotes(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user->hasPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur ini memerlukan subscription premium.',
            ], 403);
        }

        $request->validate([
            'question' => 'required|string|max:500',
            'note_ids' => 'nullable|array',
            'note_ids.*' => 'uuid|exists:notes,id',
        ]);

        $result = $this->qaService->answerQuestionAboutNotes(
            $user,
            $request->question,
            $request->note_ids ?? [],
            $request->get('limit', 10)
        );

        return response()->json($result);
    }

    /**
     * Get weekly insights.
     */
    public function getWeeklyInsights(): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user->hasPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur ini memerlukan subscription premium.',
            ], 403);
        }

        $summary = $this->insightService->generateWeeklySummary($user);
        $topics = $this->insightService->detectTopics($user);
        $statistics = $this->insightService->getNoteStatistics($user);

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'topics' => $topics,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Get detected topics.
     */
    public function getTopics(): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user->hasPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur ini memerlukan subscription premium.',
            ], 403);
        }

        $topics = $this->insightService->detectTopics($user);

        return response()->json([
            'success' => true,
            'topics' => $topics,
        ]);
    }

    /**
     * Insights dashboard page.
     */
    public function insights(): View
    {
        $user = auth()->user();
        
        if (!$user->hasPremium()) {
            abort(403, 'Fitur ini memerlukan subscription premium.');
        }

        return view('40-shared/premium/insights');
    }

    /**
     * Q&A page.
     */
    public function qa(): View
    {
        $user = auth()->user();
        
        if (!$user->hasPremium()) {
            abort(403, 'Fitur ini memerlukan subscription premium.');
        }

        $notes = $user->notes()->latest()->limit(50)->get(['id', 'title']);

        return view('40-shared/premium/qa', compact('notes'));
    }
}
