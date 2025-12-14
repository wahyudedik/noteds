<?php

namespace App\Http\Controllers;

use App\Services\ContentRecommendationEngine;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    private ContentRecommendationEngine $engine;

    public function __construct(ContentRecommendationEngine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Get personalized recommendations for authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $limit = $request->input('limit', 12);

        $recommendations = $user
            ? $this->engine->getPersonalizedRecommendations($user, $limit)
            : $this->engine->getHomepageRecommendations($limit);

        return response()->json([
            'success' => true,
            'data' => $recommendations,
        ]);
    }

    /**
     * Get similar notes
     */
    public function similar($noteId)
    {
        $note = \App\Models\Note::findOrFail($noteId);
        $similar = $this->engine->getSimilarNotes($note, 5);

        return response()->json([
            'success' => true,
            'data' => $similar,
        ]);
    }

    /**
     * Get trending content by category
     */
    public function trending(Request $request)
    {
        $category = $request->input('category', 'all');
        $limit = $request->input('limit', 8);

        $trending = $category === 'all'
            ? $this->engine->getTrendingRecommendations($limit)
            : $this->engine->getTrendingByCategory($category, $limit);

        return response()->json([
            'success' => true,
            'data' => $trending,
        ]);
    }

    /**
     * Track recommendation impression
     */
    public function trackImpression(Request $request)
    {
        $validated = $request->validate([
            'note_id' => 'required|exists:notes,id',
            'context' => 'required|string',
            'algorithm' => 'nullable|string',
            'position' => 'nullable|integer',
        ]);

        \DB::table('recommendation_impressions')->insert([
            'user_id' => $request->user()?->id,
            'note_id' => $validated['note_id'],
            'context' => $validated['context'],
            'algorithm' => $validated['algorithm'] ?? null,
            'position' => $validated['position'] ?? 0,
            'created_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Track recommendation click
     */
    public function trackClick(Request $request)
    {
        $validated = $request->validate([
            'note_id' => 'required|exists:notes,id',
            'context' => 'required|string',
            'impression_id' => 'nullable|exists:recommendation_impressions,id',
        ]);

        \DB::table('recommendation_clicks')->insert([
            'impression_id' => $validated['impression_id'] ?? null,
            'user_id' => $request->user()?->id,
            'note_id' => $validated['note_id'],
            'context' => $validated['context'],
            'created_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
