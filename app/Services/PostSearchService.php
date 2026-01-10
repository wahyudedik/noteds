<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Hashtag;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PostSearchService
{
    /**
     * Perform advanced search on posts with multiple filters.
     *
     * @param string $query
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $postsQuery = Post::with(['user', 'media', 'hashtags', 'poll.options'])
            ->where('status', 'active');

        // Text search
        if (!empty($query)) {
            $postsQuery->where(function ($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%')
                  ->orWhere('content', 'like', '%' . $query . '%');
            });
        }

        // Date range filter
        if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            if (!empty($filters['date_from'])) {
                $postsQuery->whereDate('created_at', '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $postsQuery->whereDate('created_at', '<=', $filters['date_to']);
            }
        } elseif (!empty($filters['date'])) {
            $postsQuery = $this->applyDateFilter($postsQuery, $filters['date']);
        }

        // Author filter
        if (!empty($filters['author'])) {
            $postsQuery->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['author'] . '%')
                  ->orWhere('business_name', 'like', '%' . $filters['author'] . '%');
            });
        }

        // Hashtag filter
        if (!empty($filters['hashtags'])) {
            $hashtagNames = is_array($filters['hashtags']) 
                ? $filters['hashtags'] 
                : explode(',', $filters['hashtags']);
            
            $hashtagIds = Hashtag::whereIn('name', array_map(function($tag) {
                return ltrim($tag, '#');
            }, $hashtagNames))->pluck('id');

            if ($hashtagIds->isNotEmpty()) {
                $postsQuery->whereHas('hashtags', function ($q) use ($hashtagIds) {
                    $q->whereIn('hashtags.id', $hashtagIds);
                });
            }
        }

        // Purpose type filter
        if (!empty($filters['purpose_type']) && $filters['purpose_type'] !== 'all') {
            $postsQuery->where('purpose_type', $filters['purpose_type']);
        }

        // Engagement filter
        if (!empty($filters['min_engagement'])) {
            $postsQuery->where(function ($q) use ($filters) {
                $minEngagement = (int) $filters['min_engagement'];
                $q->whereRaw('(upvotes_count + downvotes_count + comments_count + reposts_count) >= ?', [$minEngagement]);
            });
        }

        // Sort by
        $sortBy = $filters['sort_by'] ?? 'latest';
        switch ($sortBy) {
            case 'trending':
                $postsQuery->orderByDesc('trending_score')
                    ->orderByDesc('created_at');
                break;
            case 'most_engaged':
                $postsQuery->orderByRaw('(upvotes_count + downvotes_count + comments_count + reposts_count) DESC')
                    ->orderByDesc('created_at');
                break;
            case 'most_upvoted':
                $postsQuery->orderByDesc('upvotes_count')
                    ->orderByDesc('created_at');
                break;
            case 'most_commented':
                $postsQuery->orderByDesc('comments_count')
                    ->orderByDesc('created_at');
                break;
            case 'oldest':
                $postsQuery->orderBy('created_at');
                break;
            case 'latest':
            default:
                $postsQuery->latest();
                break;
        }

        return $postsQuery->paginate($perPage);
    }

    /**
     * Apply date filter to query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $dateFilter
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyDateFilter($query, string $dateFilter)
    {
        return match ($dateFilter) {
            'today' => $query->whereDate('created_at', today()),
            'week' => $query->where('created_at', '>=', now()->subWeek()),
            'month' => $query->where('created_at', '>=', now()->subMonth()),
            'year' => $query->where('created_at', '>=', now()->subYear()),
            default => $query,
        };
    }
}


