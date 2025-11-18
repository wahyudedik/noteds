<?php

namespace App\Services;

use App\Models\User;
use App\Models\Note;
use App\Models\NoteViewHistory;
use App\Models\UserPreference;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class RecommendationService
{
    /**
     * Get personalized recommendations for a user.
     */
    public function getRecommendations(User $user, int $limit = 12): array
    {
        $cacheKey = "user_recommendations_{$user->id}";
        
        return Cache::remember($cacheKey, 3600, function () use ($user, $limit) {
            $recommendations = [];
            
            // Get user preferences
            $preferences = $user->preference;
            
            // Strategy 1: Based on browsing history (most viewed categories/tags)
            $browsingBased = $this->getRecommendationsFromBrowsingHistory($user, $limit);
            $recommendations = array_merge($recommendations, $browsingBased);
            
            // Strategy 2: Based on user preferences (if set)
            if ($preferences && ($preferences->preferred_categories || $preferences->preferred_tags)) {
                $preferenceBased = $this->getRecommendationsFromPreferences($user, $preferences, $limit);
                $recommendations = array_merge($recommendations, $preferenceBased);
            }
            
            // Strategy 3: Based on similar users (collaborative filtering)
            $similarUsersBased = $this->getRecommendationsFromSimilarUsers($user, $limit);
            $recommendations = array_merge($recommendations, $similarUsersBased);
            
            // Remove duplicates and already viewed notes
            $viewedNoteIds = $user->viewHistory()->pluck('note_id')->toArray();
            $recommendations = collect($recommendations)
                ->unique('id')
                ->reject(function ($note) use ($viewedNoteIds) {
                    return is_object($note) && in_array($note->id, $viewedNoteIds);
                })
                ->take($limit)
                ->values()
                ->all();
            
            // If we don't have enough recommendations, fill with popular notes
            if (count($recommendations) < $limit) {
                $popularNotes = $this->getPopularNotes($user, $limit - count($recommendations), $viewedNoteIds);
                $recommendations = array_merge($recommendations, $popularNotes);
            }
            
            return array_slice($recommendations, 0, $limit);
        });
    }

    /**
     * Get recommendations based on browsing history.
     */
    protected function getRecommendationsFromBrowsingHistory(User $user, int $limit): array
    {
        // Get most viewed categories and tags from user's browsing history
        $viewedNotes = $user->viewHistory()
            ->with('note.tags', 'note:id,ecosystem_category')
            ->where('viewed_at', '>=', now()->subDays(30))
            ->get()
            ->pluck('note')
            ->filter();
        
        if ($viewedNotes->isEmpty()) {
            return [];
        }
        
        // Get most common categories
        $categories = $viewedNotes->pluck('ecosystem_category')->filter()->countBy()->sortDesc()->take(3)->keys();
        
        // Get most common tags
        $tagIds = $viewedNotes->flatMap(function ($note) {
            return $note->tags->pluck('id');
        })->countBy()->sortDesc()->take(5)->keys();
        
        // Find similar notes
        $query = Note::publicOnly()
            ->where('status', 'active')
            ->where('id', '!=', $viewedNotes->pluck('id')->toArray());
        
        if ($categories->isNotEmpty()) {
            $query->whereIn('ecosystem_category', $categories->toArray());
        }
        
        if ($tagIds->isNotEmpty()) {
            $query->whereHas('tags', function ($q) use ($tagIds) {
                $q->whereIn('tags.id', $tagIds->toArray());
            });
        }
        
        return $query->with(['user', 'tags', 'reviews'])
            ->orderByDesc('average_rating')
            ->orderByDesc('total_reviews')
            ->limit($limit)
            ->get()
            ->all();
    }

    /**
     * Get recommendations based on user preferences.
     */
    protected function getRecommendationsFromPreferences(User $user, UserPreference $preferences, int $limit): array
    {
        $query = Note::publicOnly()
            ->where('status', 'active');
        
        if ($preferences->preferred_categories) {
            $query->whereIn('ecosystem_category', $preferences->preferred_categories);
        }
        
        if ($preferences->preferred_tags) {
            $query->whereHas('tags', function ($q) use ($preferences) {
                $q->whereIn('tags.id', $preferences->preferred_tags);
            });
        }
        
        return $query->with(['user', 'tags', 'reviews'])
            ->orderByDesc('average_rating')
            ->orderByDesc('total_reviews')
            ->limit($limit)
            ->get()
            ->all();
    }

    /**
     * Get recommendations based on similar users (collaborative filtering).
     */
    protected function getRecommendationsFromSimilarUsers(User $user, int $limit): array
    {
        // Get users who viewed similar notes
        $userViewedNoteIds = $user->viewHistory()->pluck('note_id')->toArray();
        
        if (empty($userViewedNoteIds)) {
            return [];
        }
        
        // Find users who viewed at least 2 of the same notes
        $similarUsers = NoteViewHistory::select('user_id', DB::raw('COUNT(DISTINCT note_id) as common_views'))
            ->whereIn('note_id', $userViewedNoteIds)
            ->where('user_id', '!=', $user->id)
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->having('common_views', '>=', 2)
            ->orderByDesc('common_views')
            ->limit(10)
            ->pluck('user_id');
        
        if ($similarUsers->isEmpty()) {
            return [];
        }
        
        // Get notes viewed by similar users that current user hasn't viewed
        $recommendedNoteIds = NoteViewHistory::whereIn('user_id', $similarUsers->toArray())
            ->whereNotIn('note_id', $userViewedNoteIds)
            ->groupBy('note_id')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit($limit)
            ->pluck('note_id');
        
        if ($recommendedNoteIds->isEmpty()) {
            return [];
        }
        
        return Note::publicOnly()
            ->where('status', 'active')
            ->whereIn('id', $recommendedNoteIds->toArray())
            ->with(['user', 'tags', 'reviews'])
            ->orderByDesc('average_rating')
            ->get()
            ->toArray();
    }

    /**
     * Get popular notes as fallback.
     */
    protected function getPopularNotes(User $user, int $limit, array $excludeIds = []): array
    {
        $query = Note::publicOnly()
            ->where('status', 'active')
            ->whereNotIn('id', $excludeIds);
        
        return $query->with(['user', 'tags', 'reviews'])
            ->orderByDesc('total_reviews')
            ->orderByDesc('average_rating')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->all();
    }

    /**
     * Get recently viewed notes for a user.
     */
    public function getRecentlyViewed(User $user, int $limit = 12): array
    {
        return $user->viewHistory()
            ->with(['note.user', 'note.tags', 'note.reviews'])
            ->whereHas('note', function ($q) {
                $q->where('is_public', true)
                  ->where('status', 'active');
            })
            ->orderByDesc('viewed_at')
            ->limit($limit)
            ->get()
            ->pluck('note')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Update user preferences based on browsing history.
     */
    public function updatePreferencesFromBrowsingHistory(User $user): void
    {
        $viewedNotes = $user->viewHistory()
            ->with('note.tags', 'note:id,ecosystem_category')
            ->where('viewed_at', '>=', now()->subDays(30))
            ->get()
            ->pluck('note')
            ->filter();
        
        if ($viewedNotes->isEmpty()) {
            return;
        }
        
        // Calculate preferences
        $categories = $viewedNotes->pluck('ecosystem_category')->filter()->countBy()->sortDesc()->take(5)->keys()->toArray();
        $tagIds = $viewedNotes->flatMap(function ($note) {
            return $note->tags->pluck('id');
        })->countBy()->sortDesc()->take(10)->keys()->toArray();
        
        // Update or create preferences
        $preference = UserPreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'preferred_categories' => $categories,
                'preferred_tags' => $tagIds,
                'last_updated_at' => now(),
            ]
        );
        
        $preference->update([
            'preferred_categories' => $categories,
            'preferred_tags' => $tagIds,
            'browsing_history_summary' => [
                'total_views' => $viewedNotes->count(),
                'unique_categories' => count($categories),
                'unique_tags' => count($tagIds),
                'last_30_days' => true,
            ],
            'last_updated_at' => now(),
        ]);
        
        // Clear recommendations cache
        Cache::forget("user_recommendations_{$user->id}");
    }
}

