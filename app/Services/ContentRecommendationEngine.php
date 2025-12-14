<?php

namespace App\Services;

use App\Models\Note;
use App\Models\User;
use App\Models\NoteViewHistory;
use App\Models\PurchasedNote;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ContentRecommendationEngine
{
    /**
     * Get personalized recommendations for a user
     */
    public function getPersonalizedRecommendations(User $user, int $limit = 10): Collection
    {
        return Cache::remember("recommendations:user:{$user->id}", 3600, function () use ($user, $limit) {
            $recommendations = collect();

            // 1. Content-based filtering (similar to user's interests)
            $similarContent = $this->getContentBasedRecommendations($user, $limit);
            $recommendations = $recommendations->merge($similarContent);

            // 2. Collaborative filtering (users like you bought...)
            $collaborative = $this->getCollaborativeRecommendations($user, $limit);
            $recommendations = $recommendations->merge($collaborative);

            // 3. Trending content (what's popular)
            $trending = $this->getTrendingRecommendations($limit);
            $recommendations = $recommendations->merge($trending);

            // 4. Personalized based on profile
            $personalized = $this->getProfileBasedRecommendations($user, $limit);
            $recommendations = $recommendations->merge($personalized);

            // Remove duplicates, exclude already purchased, sort by score
            return $recommendations
                ->unique('id')
                ->filter(fn($note) => !$user->hasPurchased($note->id))
                ->sortByDesc('recommendation_score')
                ->take($limit);
        });
    }

    /**
     * Content-based filtering: Find similar content
     */
    private function getContentBasedRecommendations(User $user, int $limit): Collection
    {
        // Get user's viewing history and purchases
        $userCategories = $user->viewedNotes()
            ->pluck('notes.category_id')
            ->merge($user->purchasedNotes()->pluck('notes.category_id'))
            ->unique();

        $userTags = $user->viewedNotes()
            ->join('note_tag', 'notes.id', '=', 'note_tag.note_id')
            ->pluck('note_tag.tag_id')
            ->unique();

        // Find similar notes
        return Note::where('status', 'published')
            ->where('user_id', '!=', $user->id)
            ->where(function ($query) use ($userCategories, $userTags) {
                $query->whereIn('category_id', $userCategories)
                    ->orWhereHas('tags', fn($q) => $q->whereIn('tags.id', $userTags));
            })
            ->withCount('purchases', 'views')
            ->get()
            ->map(function ($note) {
                $note->recommendation_score = $note->purchases_count * 0.5 + $note->views_count * 0.1;
                return $note;
            })
            ->take($limit);
    }

    /**
     * Collaborative filtering: What users like you bought
     */
    private function getCollaborativeRecommendations(User $user, int $limit): Collection
    {
        // Find similar users based on purchase history
        $userPurchases = $user->purchasedNotes()->pluck('note_id')->toArray();

        if (empty($userPurchases)) {
            return collect();
        }

        // Find users with similar purchases
        $similarUserIds = DB::table('purchased_notes')
            ->whereIn('note_id', $userPurchases)
            ->where('user_id', '!=', $user->id)
            ->select('user_id')
            ->distinct()
            ->limit(50)
            ->pluck('user_id');

        // Get what those users bought
        return Note::where('status', 'published')
            ->where('user_id', '!=', $user->id)
            ->whereHas('purchases', fn($q) => $q->whereIn('purchased_notes.user_id', $similarUserIds))
            ->whereNotIn('id', $userPurchases)
            ->withCount('purchases')
            ->get()
            ->map(function ($note) {
                $note->recommendation_score = $note->purchases_count * 0.3;
                return $note;
            })
            ->take($limit);
    }

    /**
     * Trending content: Popular right now
     */
    private function getTrendingRecommendations(int $limit): Collection
    {
        $sevenDaysAgo = now()->subDays(7);

        return Note::where('status', 'published')
            ->whereHas('viewHistory', fn($q) => $q->where('created_at', '>=', $sevenDaysAgo))
            ->withCount(['viewHistory' => fn($q) => $q->where('created_at', '>=', $sevenDaysAgo)])
            ->withCount(['purchases' => fn($q) => $q->where('created_at', '>=', $sevenDaysAgo)])
            ->orderByDesc('view_history_count')
            ->get()
            ->map(function ($note) {
                $note->recommendation_score = $note->view_history_count * 0.2 + $note->purchases_count * 0.5;
                return $note;
            })
            ->take($limit);
    }

    /**
     * Profile-based recommendations
     */
    private function getProfileBasedRecommendations(User $user, int $limit): Collection
    {
        $query = Note::where('status', 'published')
            ->where('user_id', '!=', $user->id);

        // If user is a student, recommend educational content
        if ($user->profile?->user_type === 'student') {
            $query->whereHas('tags', fn($q) => $q->whereIn('name', ['tutorial', 'education', 'course', 'notes']));
        }

        // If user is entrepreneur, recommend business content
        if ($user->profile?->user_type === 'entrepreneur') {
            $query->whereHas('tags', fn($q) => $q->whereIn('name', ['business', 'startup', 'strategy', 'marketing']));
        }

        // Filter by language preference
        if ($user->language_preference) {
            $query->where('language', $user->language_preference);
        }

        // Filter by price range preference
        if ($user->price_preference_max) {
            $query->where('price', '<=', $user->price_preference_max);
        }

        return $query
            ->withCount('purchases', 'views')
            ->get()
            ->map(function ($note) {
                $note->recommendation_score = $note->purchases_count * 0.4 + $note->views_count * 0.1;
                return $note;
            })
            ->take($limit);
    }

    /**
     * Get recommendations for a specific note (users who liked this also liked...)
     */
    public function getSimilarNotes(Note $note, int $limit = 5): Collection
    {
        return Cache::remember("similar_notes:{$note->id}", 3600, function () use ($note, $limit) {
            // Get users who purchased this note
            $buyerIds = $note->purchases()->pluck('user_id');

            if ($buyerIds->isEmpty()) {
                // Fallback: get similar by category/tags
                return $this->getNotesWithSameCategoryOrTags($note, $limit);
            }

            // Get notes these users also purchased
            return Note::where('id', '!=', $note->id)
                ->where('status', 'published')
                ->whereHas('purchases', fn($q) => $q->whereIn('user_id', $buyerIds))
                ->withCount('purchases')
                ->orderByDesc('purchases_count')
                ->take($limit)
                ->get();
        });
    }

    /**
     * Get notes with same category or tags
     */
    private function getNotesWithSameCategoryOrTags(Note $note, int $limit): Collection
    {
        return Note::where('id', '!=', $note->id)
            ->where('status', 'published')
            ->where(function ($query) use ($note) {
                $query->where('category_id', $note->category_id)
                    ->orWhereHas('tags', fn($q) => $q->whereIn('tags.id', $note->tags->pluck('id')));
            })
            ->withCount('purchases', 'views')
            ->orderByDesc('purchases_count')
            ->take($limit)
            ->get();
    }

    /**
     * Get recommendations for homepage (new users)
     */
    public function getHomepageRecommendations(int $limit = 12): Collection
    {
        return Cache::remember('recommendations:homepage', 3600, function () use ($limit) {
            return Note::where('status', 'published')
                ->withCount('purchases', 'views', 'reviews')
                ->get()
                ->map(function ($note) {
                    // Calculate recommendation score
                    $note->recommendation_score =
                        $note->purchases_count * 0.5 +      // Sales impact
                        $note->views_count * 0.2 +          // Visibility
                        $note->reviews_count * 0.3;         // Social proof
                    return $note;
                })
                ->sortByDesc('recommendation_score')
                ->take($limit);
        });
    }

    /**
     * Get trending notes by category
     */
    public function getTrendingByCategory(string $category, int $limit = 8): Collection
    {
        $sevenDaysAgo = now()->subDays(7);

        return Note::where('status', 'published')
            ->whereHas('category', fn($q) => $q->where('slug', $category))
            ->whereHas('viewHistory', fn($q) => $q->where('created_at', '>=', $sevenDaysAgo))
            ->withCount([
                'viewHistory' => fn($q) => $q->where('created_at', '>=', $sevenDaysAgo),
                'purchases' => fn($q) => $q->where('created_at', '>=', $sevenDaysAgo)
            ])
            ->orderByDesc('view_history_count')
            ->take($limit)
            ->get();
    }

    /**
     * Refresh recommendations cache for user
     */
    public function refreshUserRecommendations(User $user): void
    {
        Cache::forget("recommendations:user:{$user->id}");
    }

    /**
     * Refresh all recommendations caches
     */
    public function refreshAllRecommendations(): void
    {
        Cache::tags(['recommendations'])->flush();
    }

    /**
     * Get recommendation statistics
     */
    public function getRecommendationStats(): array
    {
        return [
            'total_users_with_recs' => User::whereHas('viewHistory')->count(),
            'avg_recs_per_user' => DB::table('recommendations')->avg('note_count') ?? 0,
            'ctr_recommendation' => $this->calculateClickThroughRate(),
            'top_recommended_notes' => Note::withCount('recommendations')->orderByDesc('recommendations_count')->limit(10)->get(),
        ];
    }

    /**
     * Calculate click-through rate for recommendations
     */
    private function calculateClickThroughRate(): float
    {
        $shown = DB::table('recommendation_impressions')->count();
        if ($shown === 0) {
            return 0;
        }

        $clicked = DB::table('recommendation_clicks')->count();
        return ($clicked / $shown) * 100;
    }
}
