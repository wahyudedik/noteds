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
        $notes = $user->notes()
            ->with(['tags', 'reviews'])
            ->latest()
            ->paginate(15);

        // Statistics for dashboard
        $stats = [
            'total_notes' => $user->notes()->count(),
            'public_notes' => $user->notes()->where('is_public', true)->count(),
            'private_notes' => $user->notes()->where('is_public', false)->count(),
            'total_tags' => $user->notes()->with('tags')->get()->flatMap->tags->unique('id')->count(),
        ];

        return view('mynoteds.index', compact('notes', 'stats'));
    }

    /**
     * Display AI Q&A interface.
     */
    public function ask(): View
    {
        return view('mynoteds.ask');
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

        return view('mynoteds.search', compact('query', 'results'));
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

        return view('mynoteds.insights', compact('weeklySummary', 'topics', 'statistics'));
    }
}
