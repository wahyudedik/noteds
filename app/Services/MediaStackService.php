<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MediaStackService
{
    protected string $apiKey;
    protected string $apiEndpoint;
    protected int $cacheDuration;
    protected int $articleFreshness;

    public function __construct()
    {
        $this->apiKey = config('mediastack.api_key');
        $this->apiEndpoint = config('mediastack.api_endpoint');
        $this->cacheDuration = config('mediastack.cache_duration', 480); // 8 hours in minutes
        $this->articleFreshness = config('mediastack.article_freshness', 8); // 8 hours
    }

    /**
     * Get articles with filters (query from database first).
     */
    public function getArticles(array $filters = []): array
    {
        $cacheKey = 'articles_' . md5(serialize($filters));

        return Cache::remember($cacheKey, now()->addMinutes($this->cacheDuration), function () use ($filters) {
            $query = Article::query();

            // Apply filters
            if (isset($filters['category'])) {
                $query->byCategory($filters['category']);
            }

            if (isset($filters['search'])) {
                $query->search($filters['search']);
            }

            // Always order by recent
            $query->recent();

            // Limit results
            $limit = $filters['limit'] ?? 20;
            $articles = $query->limit($limit)->get();

            return $articles->toArray();
        });
    }

    /**
     * Search articles from database.
     */
    public function searchArticles(string $query, int $limit = 20): array
    {
        $cacheKey = 'articles_search_' . md5($query . $limit);

        return Cache::remember($cacheKey, now()->addMinutes(240), function () use ($query, $limit) {
            $articles = Article::search($query)
                ->recent()
                ->limit($limit)
                ->get();

            return $articles->toArray();
        });
    }

    /**
     * Track API usage (increment counter for current month).
     */
    protected function trackApiUsage(): void
    {
        $monthKey = 'mediastack_api_usage_' . Carbon::now()->format('Y-m');
        $currentUsage = Cache::get($monthKey, 0);
        Cache::put($monthKey, $currentUsage + 1, now()->endOfMonth());
    }

    /**
     * Get current month API usage count.
     */
    public function getCurrentMonthUsage(): int
    {
        $monthKey = 'mediastack_api_usage_' . Carbon::now()->format('Y-m');
        return Cache::get($monthKey, 0);
    }

    /**
     * Get API usage limit.
     */
    public function getApiLimit(): int
    {
        return config('mediastack.max_requests_per_month', 100);
    }

    /**
     * Check if API usage limit is reached.
     */
    public function isUsageLimitReached(): bool
    {
        return $this->getCurrentMonthUsage() >= $this->getApiLimit();
    }

    /**
     * Fetch articles from API and store to database.
     */
    public function fetchAndStoreArticles(array $params = []): array
    {
        try {
            // Check usage limit before making request
            if ($this->isUsageLimitReached()) {
                Log::warning('MediaStack API usage limit reached', [
                    'current_usage' => $this->getCurrentMonthUsage(),
                    'limit' => $this->getApiLimit(),
                ]);
                return [];
            }

            $categories = $params['categories'] ?? config('mediastack.default_categories');
            $language = $params['language'] ?? config('mediastack.default_language', 'id');
            $limit = $params['limit'] ?? config('mediastack.default_limit', 100);
            $keywords = $params['keywords'] ?? null;

            // Build API request
            $queryParams = [
                'access_key' => $this->apiKey,
                'categories' => is_array($categories) ? implode(',', $categories) : $categories,
                'languages' => $language,
                'limit' => $limit,
            ];

            if ($keywords) {
                $queryParams['keywords'] = $keywords;
            }

            // Make API request
            $response = Http::timeout(30)->get($this->apiEndpoint, $queryParams);

            // Track API usage (only on successful request)
            if ($response->successful()) {
                $this->trackApiUsage();
            }

            if (!$response->successful()) {
                Log::error('MediaStack API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            $data = $response->json();

            if (!isset($data['data']) || !is_array($data['data'])) {
                Log::warning('MediaStack API returned invalid data', ['data' => $data]);
                return [];
            }

            $articles = $data['data'];
            $storedArticles = $this->storeArticles($articles);

            Log::info('MediaStack articles fetched and stored', [
                'count' => count($storedArticles),
            ]);

            return $storedArticles;
        } catch (\Exception $e) {
            Log::error('MediaStack fetch error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return [];
        }
    }

    /**
     * Store articles to database.
     */
    public function storeArticles(array $articles): array
    {
        $storedArticles = [];
        $now = Carbon::now();

        foreach ($articles as $articleData) {
            try {
                // Skip if URL is empty
                if (empty($articleData['url'])) {
                    continue;
                }

                // Parse published_at
                $publishedAt = null;
                if (isset($articleData['published_at'])) {
                    try {
                        $publishedAt = Carbon::parse($articleData['published_at']);
                    } catch (\Exception $e) {
                        // Invalid date, skip
                    }
                }

                // Generate URL hash for uniqueness check
                $urlHash = hash('sha256', $articleData['url']);

                // Check if article already exists
                $existingArticle = Article::where('url_hash', $urlHash)->first();

                if ($existingArticle) {
                    // Update existing article
                    $existingArticle->update([
                        'title' => $articleData['title'] ?? '',
                        'description' => $articleData['description'] ?? null,
                        'url' => $articleData['url'],
                        'source' => $articleData['source'] ?? '',
                        'image' => $articleData['image'] ?? null,
                        'category' => $articleData['category'] ?? null,
                        'author' => $articleData['author'] ?? null,
                        'published_at' => $publishedAt,
                        'language' => $articleData['language'] ?? 'id',
                        'country' => $articleData['country'] ?? null,
                        'raw_data' => $articleData,
                        'fetched_at' => $now,
                    ]);
                    $storedArticles[] = $existingArticle;
                } else {
                    // Create new article
                    $article = Article::create([
                        'title' => $articleData['title'] ?? '',
                        'description' => $articleData['description'] ?? null,
                        'url' => $articleData['url'],
                        'url_hash' => $urlHash,
                        'source' => $articleData['source'] ?? '',
                        'image' => $articleData['image'] ?? null,
                        'category' => $articleData['category'] ?? null,
                        'author' => $articleData['author'] ?? null,
                        'published_at' => $publishedAt,
                        'language' => $articleData['language'] ?? 'id',
                        'country' => $articleData['country'] ?? null,
                        'raw_data' => $articleData,
                        'fetched_at' => $now,
                    ]);
                    $storedArticles[] = $article;
                }
            } catch (\Exception $e) {
                Log::error('Failed to store article: ' . $e->getMessage(), [
                    'article' => $articleData,
                    'exception' => $e,
                ]);
                continue;
            }
        }

        return $storedArticles;
    }

    /**
     * Check if should fetch from API for a category.
     */
    public function shouldFetchFromAPI(?string $category = null): bool
    {
        // Check if database has fresh articles
        $query = Article::query();
        
        if ($category) {
            $query->byCategory($category);
        }

        $latestArticle = $query->latest('fetched_at')->first();

        if (!$latestArticle) {
            // No articles in database, need to fetch
            return true;
        }

        // Check if articles are stale
        return $latestArticle->isStale($this->articleFreshness);
    }

    /**
     * Get categories from config.
     */
    public function getCategories(): array
    {
        return config('mediastack.default_categories', []);
    }
}

