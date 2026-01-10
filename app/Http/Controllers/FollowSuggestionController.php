<?php

namespace App\Http\Controllers;

use App\Services\FollowSuggestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FollowSuggestionController extends Controller
{
    public function __construct(
        private FollowSuggestionService $suggestionService
    ) {}

    /**
     * Get AI-powered follow suggestions.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $user = $request->user();
        $limit = (int) $request->query('limit', 10);
        $categoryFilter = $request->query('category');

        $suggestions = $this->suggestionService->getSuggestions($user, $limit);

        // Filter by category if specified
        if ($categoryFilter) {
            $suggestions = $suggestions->filter(function ($suggestion) use ($categoryFilter) {
                return $suggestion['user']->categories()
                    ->where('categories.slug', $categoryFilter)
                    ->exists();
            })->values();
        }

        // Format suggestions for frontend
        $formattedSuggestions = $suggestions->map(function ($suggestion) use ($user) {
            $suggestedUser = $suggestion['user'];
            
            // Get mutual connections count
            $mutualConnections = app(\App\Services\FollowService::class)
                ->getMutualConnections($user, $suggestedUser);
            
            return [
                'user' => $suggestedUser->only(['id', 'name', 'business_name', 'avatar', 'business_field', 'is_verified_mentor']),
                'avatar_url' => $suggestedUser->avatar_url,
                'scores' => $suggestion['scores'],
                'final_score' => round($suggestion['final_score'], 3),
                'mutual_connections_count' => $mutualConnections->count(),
                'mutual_connections' => $mutualConnections->take(5)->map(fn($u) => [
                    'id' => $u->id,
                    'name' => $u->business_name ?? $u->name,
                    'avatar_url' => $u->avatar_url,
                ]),
                'categories' => $suggestedUser->categories->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'icon' => $c->icon,
                ]),
            ];
        });

        if ($request->wantsJson()) {
            return response()->json([
                'suggestions' => $formattedSuggestions,
            ]);
        }

        return Inertia::render('Follow/Suggestions', [
            'suggestions' => $formattedSuggestions,
            'categories' => \App\Models\Category::active()->ordered()->get()->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'icon' => $c->icon,
            ]),
        ]);
    }

    /**
     * Refresh suggestions (clear cache).
     */
    public function refresh(Request $request): JsonResponse|Response
    {
        $user = $request->user();
        $this->suggestionService->clearCache($user);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Suggestions refreshed successfully.',
            ]);
        }

        return redirect()->route('follow.suggestions')->with('success', 'Suggestions refreshed successfully.');
    }
}
