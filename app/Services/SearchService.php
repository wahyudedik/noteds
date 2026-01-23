<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Models\Product;
use App\Models\Article;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchService
{
    /**
     * Perform global search across posts, users, products, and articles.
     *
     * @param string $query
     * @param array $filters
     * @param int $perPage
     * @return array
     */
    public function search(string $query, array $filters = [], int $perPage = 15): array
    {
        $results = [
            'posts' => collect(),
            'users' => collect(),
            'products' => collect(),
            'articles' => collect(),
        ];

        $type = $filters['type'] ?? 'all';
        $dateFilter = $filters['date'] ?? null;
        $category = $filters['category'] ?? null;

        // Search posts
        if ($type === 'all' || $type === 'posts') {
            $results['posts'] = $this->searchPosts($query, $dateFilter, $category, $perPage);
        }

        // Search users
        if ($type === 'all' || $type === 'users') {
            $results['users'] = $this->searchUsers($query, $perPage);
        }

        // Search products
        if ($type === 'all' || $type === 'products') {
            $results['products'] = $this->searchProducts($query, $category, $perPage);
        }

        // Search articles
        if ($type === 'all' || $type === 'articles') {
            $results['articles'] = $this->searchArticles($query, $category, $perPage);
        }

        return $results;
    }

    /**
     * Search posts.
     *
     * @param string $query
     * @param string|null $dateFilter
     * @param string|null $category
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    protected function searchPosts(string $query, ?string $dateFilter = null, ?string $category = null, int $perPage = 15): LengthAwarePaginator
    {
        $postsQuery = Post::with('user')
            ->where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%')
                    ->orWhere('content', 'like', '%' . $query . '%');
            });

        // Apply date filter
        if ($dateFilter) {
            $postsQuery = $this->applyDateFilter($postsQuery, $dateFilter);
        }

        // Apply category filter (purpose_type)
        if ($category) {
            $postsQuery->where('purpose_type', $category);
        }

        return $postsQuery->latest()->paginate($perPage);
    }

    /**
     * Search users.
     *
     * @param string $query
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchUsers(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return User::where(function ($q) use ($query) {
            $q->where('name', 'like', '%' . $query . '%')
                ->orWhere('email', 'like', '%' . $query . '%')
                ->orWhere('business_name', 'like', '%' . $query . '%');
        })
            ->where('is_banned', false)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Search products.
     *
     * @param string $query
     * @param string|null $category
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchProducts(string $query, ?string $category = null, int $perPage = 15): LengthAwarePaginator
    {
        $productsQuery = Product::with('seller')
            ->active()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                    ->orWhere('description', 'like', '%' . $query . '%');
            });

        if ($category) {
            $productsQuery->where('category', $category);
        }

        return $productsQuery->latest()->paginate($perPage);
    }

    /**
     * Search articles.
     *
     * @param string $query
     * @param string|null $category
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchArticles(string $query, ?string $category = null, int $perPage = 15): LengthAwarePaginator
    {
        $articlesQuery = Article::query()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%')
                    ->orWhere('description', 'like', '%' . $query . '%');
            });

        if ($category) {
            $articlesQuery->where('category', $category);
        }

        return $articlesQuery->recent()->paginate($perPage);
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

    /**
     * Get search suggestions/autocomplete.
     *
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function getSuggestions(string $query, int $limit = 10, ?\App\Models\User $user = null): array
    {
        $suggestions = [];
        $perTypeLimit = max(3, ceil($limit / 3)); // At least 3 per type

        // Post titles
        $postTitles = Post::where('status', 'active')
            ->where('title', 'like', '%' . $query . '%')
            ->limit($perTypeLimit)
            ->pluck('title')
            ->toArray();
        $suggestions = array_merge($suggestions, $postTitles);

        // User names and business names
        $users = User::where('is_banned', false)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                    ->orWhere('business_name', 'like', '%' . $query . '%');
            })
            ->limit($perTypeLimit)
            ->get();

        foreach ($users as $user) {
            if ($user->business_name && stripos($user->business_name, $query) !== false) {
                $suggestions[] = $user->business_name;
            } elseif ($user->name) {
                $suggestions[] = $user->name;
            }
        }

        // Product names
        $productNames = Product::active()
            ->where('name', 'like', '%' . $query . '%')
            ->limit($perTypeLimit)
            ->pluck('name')
            ->toArray();
        $suggestions = array_merge($suggestions, $productNames);

        if ($user) {
            $history = \App\Models\SearchHistory::where('user_id', $user->id)
                ->where('query', 'like', '%' . $query . '%')
                ->orderBy('created_at', 'desc')
                ->limit($perTypeLimit)
                ->pluck('query')
                ->toArray();
            $suggestions = array_merge($suggestions, $history);

            $saved = \App\Models\SavedSearch::where('user_id', $user->id)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', '%' . $query . '%')
                        ->orWhere('query', 'like', '%' . $query . '%');
                })
                ->orderBy('created_at', 'desc')
                ->limit($perTypeLimit)
                ->pluck('name')
                ->toArray();
            $suggestions = array_merge($suggestions, $saved);
        }

        // Remove duplicates and limit results
        $uniqueSuggestions = array_unique($suggestions);
        return array_slice($uniqueSuggestions, 0, $limit);
    }
}
