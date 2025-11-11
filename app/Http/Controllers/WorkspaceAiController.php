<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\User;
use App\Services\AiService;
use App\Services\AiMemoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceAiController extends Controller
{
    public function __construct(
        protected AiService $aiService,
        protected AiMemoryService $aiMemoryService
    ) {
        $this->middleware('auth');
        $this->middleware('verified');
        $this->middleware('username.setup');
        $this->middleware('ai.access');
    }

    /**
     * Display AI Chat interface for workspace.
     * AI will use all notes in the workspace as context.
     */
    public function chat(Workspace $workspace): View
    {
        $user = auth()->user();
        
        // Check workspace access
        if ($workspace->owner_id !== $user->id && !$workspace->hasMember($user)) {
            abort(403, 'Anda tidak memiliki akses ke workspace ini.');
        }

        // Get workspace notes for context
        $workspaceNotes = $workspace->notes()
            ->where('status', 'active')
            ->with(['tags'])
            ->latest()
            ->limit(100)
            ->get(['id', 'title', 'summary', 'content']);

        return view('workspaces.ai-chat', compact('workspace', 'workspaceNotes', 'user'));
    }

    /**
     * Send message to AI about workspace notes.
     */
    public function sendMessage(Request $request, Workspace $workspace): JsonResponse
    {
        $user = auth()->user();
        
        // Check workspace access
        if ($workspace->owner_id !== $user->id && !$workspace->hasMember($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke workspace ini.',
            ], 403);
        }

        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        // Use AI Memory Service to answer with workspace context
        $result = $this->aiMemoryService->answerWithKnowledgeBase(
            $user,
            $request->message,
            $workspace,
            100 // Max notes from workspace
        );

        return response()->json($result);
    }
}

