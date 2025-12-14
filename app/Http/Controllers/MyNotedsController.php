<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyNotedsController extends Controller
{
    /**
     * Display the MyNoteds dashboard (AI Memory Platform).
     * Premium feature - middleware ensures user has premium.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Get user's notes for AI Memory Platform
        $notesQuery = $user->notes()->with(['tags', 'reviews', 'folder', 'workspace']);
        
        // Filter by workspace if provided
        if ($request->has('workspace_id') && $request->workspace_id) {
            if ($request->workspace_id === 'personal') {
                $notesQuery->whereNull('workspace_id');
            } else {
                $notesQuery->where('workspace_id', $request->workspace_id);
            }
        }
        
        $notes = $notesQuery->latest()->paginate(15);

        // Statistics for dashboard (filtered if workspace selected)
        $baseQuery = $user->notes();
        if ($request->has('workspace_id') && $request->workspace_id) {
            if ($request->workspace_id === 'personal') {
                $baseQuery->whereNull('workspace_id');
            } else {
                $baseQuery->where('workspace_id', $request->workspace_id);
            }
        }
        
        $stats = [
            'total_notes' => (clone $baseQuery)->count(),
            'public_notes' => (clone $baseQuery)->where('is_public', true)->count(),
            'private_notes' => (clone $baseQuery)->where('is_public', false)->count(),
            'total_tags' => (clone $baseQuery)->with('tags')->get()->flatMap->tags->unique('id')->count(),
        ];

        return view('40-shared/mynoteds/index', compact('notes', 'stats'));
    }

    /**
     * Display AI Q&A interface.
     */
    public function ask(): View
    {
        return view('40-shared/mynoteds/ask');
    }

    /**
     * Display semantic search interface.
     */
    public function search(Request $request): View
    {
        $query = $request->get('q', '');
        $results = collect([]);

        // Basic search (fallback if semantic search not available)
        // Semantic search API is available via /ai-memory/search endpoint
        if ($query) {
            $user = $request->user();
            $results = $user->notes()
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%");
                })
                ->with(['tags'])
                ->paginate(10);
        }

        return view('40-shared/mynoteds/search', compact('query', 'results'));
    }

    /**
     * Display insights dashboard.
     */
    public function insights(Request $request): View
    {
        $user = $request->user();
        
        // Get insights data
        $insightService = app(\App\Services\AiInsightService::class);
        
        $weeklySummary = null;
        $topics = [];
        $statistics = [];
        
        // Only generate AI insights if Ollama is available
        if ($insightService->isAiServiceAvailable()) {
            try {
                $weeklySummary = $insightService->generateWeeklySummary($user);
                $topics = $insightService->detectTopics($user);
            } catch (\Exception $e) {
                // Silently fail, show basic stats only
                logger()->error('AI Insights generation failed', ['error' => $e->getMessage()]);
            }
        }
        
        // Always show basic statistics
        $statistics = $insightService->getNoteStatistics($user);

        return view('40-shared/mynoteds/insights', compact('weeklySummary', 'topics', 'statistics'));
    }
}
