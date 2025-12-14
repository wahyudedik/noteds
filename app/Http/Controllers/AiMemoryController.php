<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\User;
use App\Models\Workspace;
use App\Services\AiMemoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiMemoryController extends Controller
{
    public function __construct(
        protected AiMemoryService $aiMemoryService
    ) {
        $this->middleware('auth');
        $this->middleware('verified');
        $this->middleware('premium'); // Premium feature
    }

    /**
     * AI Memory Platform dashboard.
     */
    public function index(): View
    {
        $user = auth()->user();
        
        // Get knowledge base stats
        $knowledgeBase = $this->aiMemoryService->buildKnowledgeBase($user, null, 100);
        
        return view('40-shared/ai-memory/index', [
            'total_notes' => $knowledgeBase['total_notes'] ?? 0,
            'topics' => array_slice($knowledgeBase['topics'] ?? [], 0, 20),
        ]);
    }

    /**
     * Ask question using AI Memory (knowledge base from all notes).
     */
    public function ask(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $request->validate([
            'question' => 'required|string|max:500',
            'workspace_id' => 'nullable|uuid|exists:workspaces,id',
        ]);

        $workspace = null;
        if ($request->workspace_id) {
            $workspace = Workspace::where('id', $request->workspace_id)
                ->where(function($q) use ($user) {
                    $q->where('owner_id', $user->id)
                      ->orWhereHas('members', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
                })
                ->first();
        }

        $result = $this->aiMemoryService->answerWithKnowledgeBase(
            $user,
            $request->question,
            $workspace,
            $request->get('max_notes', 50)
        );

        return response()->json($result);
    }

    /**
     * Get contextually linked notes.
     */
    public function getContextualLinks(Note $note): JsonResponse
    {
        $user = auth()->user();
        
        // Check access
        if ($note->user_id !== $user->id && !$note->is_public) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke catatan ini.',
            ], 403);
        }

        $relatedNotes = $this->aiMemoryService->findContextualLinks($user, $note, 10);

        return response()->json([
            'success' => true,
            'related_notes' => $relatedNotes->map(fn($n) => [
                'id' => $n->id,
                'title' => $n->title,
                'summary' => substr(strip_tags($n->content), 0, 200),
                'created_at' => $n->created_at->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Generate insights from user's notes.
     */
    public function generateInsights(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $workspace = null;
        if ($request->workspace_id) {
            $workspace = Workspace::where('id', $request->workspace_id)
                ->where(function($q) use ($user) {
                    $q->where('owner_id', $user->id)
                      ->orWhereHas('members', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
                })
                ->first();
        }

        $result = $this->aiMemoryService->generateInsights($user, $workspace);

        return response()->json($result);
    }

    /**
     * Build knowledge base (can be triggered manually or via job).
     */
    public function buildKnowledgeBase(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $workspace = null;
        if ($request->workspace_id) {
            $workspace = Workspace::where('id', $request->workspace_id)
                ->where(function($q) use ($user) {
                    $q->where('owner_id', $user->id)
                      ->orWhereHas('members', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
                })
                ->first();
        }

        // Dispatch job to build knowledge base in background
        \App\Jobs\ProcessAiRequestWithRetry::dispatch(
            'build_knowledge_base',
            [
                'user_id' => $user->id,
                'workspace_id' => $workspace?->id,
            ]
        )->onQueue('ai');

        return response()->json([
            'success' => true,
            'message' => 'Knowledge base building started. This may take a few moments.',
        ]);
    }

    /**
     * Get knowledge base statistics.
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $workspace = null;
        if ($request->workspace_id) {
            $workspace = Workspace::where('id', $request->workspace_id)
                ->where(function($q) use ($user) {
                    $q->where('owner_id', $user->id)
                      ->orWhereHas('members', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
                })
                ->first();
        }

        $knowledgeBase = $this->aiMemoryService->buildKnowledgeBase($user, $workspace, 1000);

        return response()->json([
            'success' => true,
            'stats' => [
                'total_notes' => $knowledgeBase['total_notes'] ?? 0,
                'topics_count' => count($knowledgeBase['topics'] ?? []),
                'topics' => array_slice($knowledgeBase['topics'] ?? [], 0, 50),
                'timeline_entries' => count($knowledgeBase['timeline'] ?? []),
            ],
        ]);
    }

    /**
     * Prepare training data for AI model fine-tuning.
     */
    public function prepareTrainingData(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya admin yang dapat menyiapkan training data.',
            ], 403);
        }

        $workspace = null;
        if ($request->workspace_id) {
            $workspace = Workspace::find($request->workspace_id);
        }

        $result = $this->aiMemoryService->prepareTrainingData($user, $workspace);

        return response()->json($result);
    }
}
