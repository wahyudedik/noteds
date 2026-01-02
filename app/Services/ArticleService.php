<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ArticleService
{
    /**
     * Fetch articles from RSS feed.
     */
    public function fetchFromRSS(array $feedConfig): array
    {
        try {
            $response = Http::timeout(30)->get($feedConfig['url']);
            
            if (!$response->successful()) {
                Log::warning("Failed to fetch RSS feed: {$feedConfig['url']}", [
                    'status' => $response->status(),
                ]);
                return [];
            }

            // Use DOMDocument for better XML parsing
            libxml_use_internal_errors(true);
            $xml = @simplexml_load_string($response->body());
            if (!$xml) {
                $errors = libxml_get_errors();
                libxml_clear_errors();
                Log::warning("Failed to parse RSS XML: {$feedConfig['url']}", ['errors' => $errors]);
                return [];
            }

            $articles = [];
            $maxArticles = config('articles.sync.max_articles_per_source', 25);
            $count = 0;

            // Handle RSS 2.0 and Atom formats
            $items = [];
            if (isset($xml->channel->item)) {
                $items = $xml->channel->item;
            } elseif (isset($xml->item)) {
                $items = $xml->item;
            } elseif (isset($xml->entry)) {
                $items = $xml->entry;
            }

            foreach ($items as $item) {
                if ($count >= $maxArticles) break;

                try {
                    $title = trim((string) ($item->title ?? ''));
                    $description = trim((string) ($item->description ?? $item->summary ?? $item->content ?? ''));
                    
                    // Get link - try different possible locations
                    $link = '';
                    if (isset($item->link)) {
                        if (is_string($item->link)) {
                            $link = trim($item->link);
                        } elseif (isset($item->link->attributes()->href)) {
                            $link = trim((string) $item->link->attributes()->href);
                        } else {
                            $link = trim((string) $item->link);
                        }
                    } elseif (isset($item->guid)) {
                        $link = trim((string) $item->guid);
                    } elseif (isset($item->id)) {
                        $link = trim((string) $item->id);
                    }

                    if (empty($title) || empty($link)) {
                        continue;
                    }

                    $pubDate = now();
                    if (isset($item->pubDate)) {
                        $dateStr = (string) $item->pubDate;
                        $timestamp = @strtotime($dateStr);
                        if ($timestamp !== false) {
                            $pubDate = date('Y-m-d H:i:s', $timestamp);
                        }
                    } elseif (isset($item->published)) {
                        $dateStr = (string) $item->published;
                        $timestamp = @strtotime($dateStr);
                        if ($timestamp !== false) {
                            $pubDate = date('Y-m-d H:i:s', $timestamp);
                        }
                    }

                    $category = (string) ($item->category ?? $feedConfig['category'] ?? 'Business');
                    $image = $this->extractImageFromRSS($item);

                    $articles[] = [
                        'title' => $title,
                        'description' => $this->cleanDescription($description),
                        'url' => $link,
                        'url_hash' => hash('sha256', $link),
                        'source' => $feedConfig['source'] ?? 'RSS Feed',
                        'image' => $image,
                        'category' => $this->mapCategory($category),
                        'author' => (string) ($item->author ?? $feedConfig['source'] ?? null),
                        'published_at' => $pubDate,
                        'language' => $feedConfig['language'] ?? 'en',
                        'fetched_at' => now(),
                    ];

                    $count++;
                } catch (\Exception $e) {
                    Log::warning("Error processing RSS item: " . $e->getMessage());
                    continue;
                }
            }

            return $articles;
        } catch (\Exception $e) {
            Log::error("Error fetching RSS feed {$feedConfig['url']}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch articles from Reddit subreddit.
     */
    public function fetchFromReddit(array $subredditConfig): array
    {
        try {
            $url = "https://www.reddit.com/r/{$subredditConfig['subreddit']}/hot.json?limit=25";
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'Noteds Explorer Bot 1.0',
                ])
                ->get($url);

            if (!$response->successful()) {
                Log::warning("Failed to fetch Reddit: r/{$subredditConfig['subreddit']}", [
                    'status' => $response->status(),
                ]);
                return [];
            }

            $data = $response->json();
            if (!isset($data['data']['children'])) {
                return [];
            }

            $articles = [];
            $minUpvotes = $subredditConfig['min_upvotes'] ?? 10;

            foreach ($data['data']['children'] as $child) {
                try {
                    $post = $child['data'] ?? null;
                    if (!$post) continue;
                    
                    // Filter by upvotes
                    $upvotes = (int) ($post['ups'] ?? 0);
                    if ($upvotes < $minUpvotes) {
                        continue;
                    }

                    // Skip if it's a self-post without external link
                    $url = $post['url'] ?? '';
                    if (empty($url) || strpos($url, 'reddit.com') !== false || strpos($url, '/r/') !== false) {
                        continue;
                    }

                    $title = trim($post['title'] ?? '');
                    $description = trim($post['selftext'] ?? '');

                    if (empty($title) || empty($url)) {
                        continue;
                    }

                    // Only use thumbnail if it's a valid image URL
                    $thumbnail = $post['thumbnail'] ?? null;
                    if ($thumbnail && (strpos($thumbnail, 'http') === false || $thumbnail === 'self' || $thumbnail === 'default')) {
                        $thumbnail = null;
                    }

                    $articles[] = [
                        'title' => $title,
                        'description' => $this->cleanDescription($description),
                        'url' => $url,
                        'url_hash' => hash('sha256', $url),
                        'source' => $subredditConfig['source'] ?? "Reddit - r/{$subredditConfig['subreddit']}",
                        'image' => $thumbnail,
                        'category' => $subredditConfig['category'] ?? 'Business',
                        'author' => $post['author'] ?? null,
                        'published_at' => isset($post['created_utc']) ? date('Y-m-d H:i:s', (int) $post['created_utc']) : now(),
                        'language' => 'en',
                        'fetched_at' => now(),
                    ];
                } catch (\Exception $e) {
                    Log::warning("Error processing Reddit post: " . $e->getMessage());
                    continue;
                }
            }

            return $articles;
        } catch (\Exception $e) {
            Log::error("Error fetching Reddit r/{$subredditConfig['subreddit']}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Sync articles from all configured sources.
     */
    public function syncArticles(string $source = 'all'): int
    {
        $totalSynced = 0;

        if ($source === 'all' || $source === 'rss') {
            $rssFeeds = config('articles.rss_feeds', []);
            foreach ($rssFeeds as $feedConfig) {
                $articles = $this->fetchFromRSS($feedConfig);
                $synced = $this->storeArticles($articles);
                $totalSynced += $synced;
                Log::info("Synced {$synced} articles from RSS: {$feedConfig['source']}");
            }
        }

        if ($source === 'all' || $source === 'reddit') {
            $subreddits = config('articles.reddit_subreddits', []);
            foreach ($subreddits as $subredditConfig) {
                $articles = $this->fetchFromReddit($subredditConfig);
                $synced = $this->storeArticles($articles);
                $totalSynced += $synced;
                Log::info("Synced {$synced} articles from Reddit: r/{$subredditConfig['subreddit']}");
            }
        }

        // Clear cache after sync
        $this->clearCache();

        return $totalSynced;
    }

    /**
     * Store articles to database (deduplicate by url_hash).
     */
    protected function storeArticles(array $articles): int
    {
        $stored = 0;

        foreach ($articles as $articleData) {
            try {
                Article::updateOrCreate(
                    ['url_hash' => $articleData['url_hash']],
                    $articleData
                );
                $stored++;
            } catch (\Exception $e) {
                Log::error("Failed to store article: " . $e->getMessage(), [
                    'title' => $articleData['title'] ?? 'Unknown',
                ]);
            }
        }

        return $stored;
    }

    /**
     * Extract image URL from RSS item.
     */
    protected function extractImageFromRSS($item): ?string
    {
        // Try media:content
        if (isset($item->children('media', true)->content)) {
            $media = $item->children('media', true)->content;
            if (isset($media->attributes()->url)) {
                return (string) $media->attributes()->url;
            }
        }

        // Try enclosure
        if (isset($item->enclosure)) {
            $type = (string) $item->enclosure->attributes()->type ?? '';
            if (strpos($type, 'image') !== false) {
                return (string) $item->enclosure->attributes()->url;
            }
        }

        // Try to extract from description HTML
        if (isset($item->description)) {
            $description = (string) $item->description;
            if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $description, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Clean and truncate description.
     */
    protected function cleanDescription(string $description, int $maxLength = 500): string
    {
        // Remove HTML tags
        $description = strip_tags($description);
        
        // Decode HTML entities
        $description = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
        
        // Trim whitespace
        $description = trim($description);
        
        // Truncate if too long
        if (mb_strlen($description) > $maxLength) {
            $description = mb_substr($description, 0, $maxLength) . '...';
        }

        return $description;
    }

    /**
     * Map category to internal category.
     */
    protected function mapCategory(?string $category): string
    {
        if (empty($category)) {
            return 'Business';
        }

        $category = strtolower(trim($category));
        $mapping = config('articles.category_mapping', []);

        foreach ($mapping as $key => $value) {
            if (strpos($category, $key) !== false) {
                return $value;
            }
        }

        // Capitalize first letter as fallback
        return ucfirst($category);
    }

    /**
     * Auto-categorize article based on title and description.
     */
    public function categorizeArticle(string $title, string $description): string
    {
        $text = strtolower($title . ' ' . $description);
        
        $keywords = [
            'startup' => 'Startup',
            'entrepreneur' => 'Entrepreneurship',
            'marketing' => 'Marketing',
            'finance' => 'Finance',
            'technology' => 'Technology',
            'tech' => 'Technology',
            'strategy' => 'Strategy',
            'business' => 'Business',
        ];

        foreach ($keywords as $keyword => $category) {
            if (strpos($text, $keyword) !== false) {
                return $category;
            }
        }

        return 'Business';
    }

    /**
     * Clear article-related cache.
     */
    public function clearCache(): void
    {
        \Illuminate\Support\Facades\Cache::forget('explorer_categories');
        \Illuminate\Support\Facades\Cache::forget('explorer_articles');
    }
}

