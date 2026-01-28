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
        $this->apiKey = (string) config('mediastack.api_key');
        $this->apiEndpoint = (string) config('mediastack.api_endpoint');
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
        $lock = Cache::lock($monthKey . '_lock', 5);
        try {
            $lock->block(5);
            $currentUsage = Cache::get($monthKey, 0);
            Cache::put($monthKey, $currentUsage + 1, now()->endOfMonth());
            // Store timestamp log (append)
            $tsKey = $monthKey . '_timestamps';
            $timestamps = Cache::get($tsKey, []);
            $timestamps[] = Carbon::now()->toIso8601String();
            Cache::put($tsKey, $timestamps, now()->endOfMonth());
        } finally {
            optional($lock)->release();
        }
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
            // Validate API key
            if (!$this->isApiKeyValid()) {
                Log::warning('API key tidak ditemukan, melewatkan pengambilan artikel');
                return [];
            }
            // Check usage limit before making request
            if ($this->isUsageLimitReached()) {
                Log::warning('MediaStack API usage limit reached', [
                    'current_usage' => $this->getCurrentMonthUsage(),
                    'limit' => $this->getApiLimit(),
                ]);
                return [];
            }

            $categories = $this->sanitizeCategories($params['categories'] ?? config('mediastack.default_categories'));
            $language = $params['language'] ?? config('mediastack.default_language', 'id');
            $limit = $params['limit'] ?? config('mediastack.default_limit', 100);
            $keywords = $params['keywords'] ?? null;

            // Build base query params
            $baseParams = [
                'access_key' => $this->apiKey,
                'categories' => is_array($categories) ? implode(',', $categories) : $categories,
                'limit' => $limit,
            ];
            if ($keywords) {
                $baseParams['keywords'] = $keywords;
            }

            $supportsMulti = (bool) config('mediastack.supports_multi_language', false);
            $languages = $supportsMulti ? $language : (is_string($language) ? explode(',', $language) : (array) $language);
            $languages = array_values(array_filter(array_map('trim', is_array($languages) ? $languages : [$languages])));

            $articles = [];
            if ($supportsMulti) {
                $queryParams = $baseParams + ['languages' => $language];
                $response = $this->performRequest($queryParams);
                if (!$response) {
                    return [];
                }
                $data = $response->json();
                if (isset($data['data']) && is_array($data['data'])) {
                    $articles = array_merge($articles, $data['data']);
                }
            } else {
                foreach ($languages as $lang) {
                    $queryParams = $baseParams + ['languages' => $lang];
                    $response = $this->performRequest($queryParams);
                    if (!$response) {
                        continue;
                    }
                    $data = $response->json();
                    if (isset($data['data']) && is_array($data['data'])) {
                        $articles = array_merge($articles, $data['data']);
                    }
                    // Rate limiting delay
                    $delayMs = (int) config('mediastack.request_delay_ms', 500);
                    if ($delayMs > 0) {
                        usleep($delayMs * 1000);
                    }
                }
            }

            if (empty($articles)) {
                Log::warning('MediaStack API returned no articles', ['languages' => $languages]);
                return [];
            }

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
        return $this->sanitizeCategories(config('mediastack.default_categories', []));
    }

    protected function isApiKeyValid(): bool
    {
        $key = $this->apiKey;
        if (empty($key)) {
            return false;
        }
        $len = strlen($key);
        if ($len < 24 || $len > 256) {
            Log::warning('MediaStack API key length invalid');
            return false;
        }
        if (!preg_match('/^[A-Za-z0-9_]+$/', $key)) {
            Log::warning('MediaStack API key contains invalid characters');
            return false;
        }
        return true;
    }

    protected function sanitizeCategories($categories): array
    {
        $allowed = config('mediastack.allowed_categories', []);
        $list = is_array($categories) ? $categories : explode(',', (string) $categories);
        $list = array_values(array_filter(array_map('trim', $list)));
        $valid = array_values(array_intersect($list, $allowed));
        if (empty($valid)) {
            Log::warning('MediaStack categories invalid, falling back to default', ['requested' => $list]);
            $valid = config('mediastack.default_categories', $allowed);
        }
        return $valid;
    }

    protected function performRequest(array $queryParams): ?\Illuminate\Http\Client\Response
    {
        $timeout = 30;
        $verify = (bool) config('mediastack.verify_ssl', true);
        $maxTries = (int) config('mediastack.retry_times', 3);
        $baseSleepMs = (int) config('mediastack.retry_sleep_ms', 1000);

        $lastResponse = null;
        for ($attempt = 1; $attempt <= $maxTries; $attempt++) {
            $response = Http::withOptions(['verify' => $verify])
                ->timeout($timeout)
                ->get($this->apiEndpoint, $queryParams);

            $lastResponse = $response;
            if ($response->successful()) {
                $this->trackApiUsage();
                return $response;
            }

            $status = $response->status();
            if ($status === 429) {
                $retryAfter = (int) ($response->header('Retry-After') ?? 1);
                usleep(max(1, $retryAfter) * 1000 * 1000);
                continue;
            }

            if (in_array($status, [500, 502, 503, 504], true)) {
                $sleepMs = (int) ($baseSleepMs * pow(2, ($attempt - 1)));
                usleep($sleepMs * 1000);
                continue;
            }

            break;
        }

        Log::error('MediaStack API request failed after retries', [
            'status' => $lastResponse ? $lastResponse->status() : null,
            'body' => $lastResponse ? $lastResponse->body() : null,
        ]);
        throw new \RuntimeException('MediaStack request failed after retries');
    }
}
