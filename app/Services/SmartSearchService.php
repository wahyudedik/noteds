<?php

namespace App\Services;

use App\Models\Note;
use App\Models\Tag;
use App\Models\User;
use App\Services\EmbeddingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SmartSearchService
{
    public function __construct(
        protected EmbeddingService $embeddingService
    ) {}

    /**
     * Smart search for notes with multiple search strategies.
     * Premium feature with enhanced search capabilities.
     */
    public function search(string $query, ?User $user = null, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = trim($query);
        
        // Base query - user's notes or public notes
        $notesQuery = Note::query();

        if ($user) {
            // User can search their own notes + public notes
            $notesQuery->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('is_public', true);
            });
        } else {
            // Guest can only see public notes
            $notesQuery->where('is_public', true);
        }

        // Apply filters
        if (isset($filters['workspace_id'])) {
            $notesQuery->where('workspace_id', $filters['workspace_id']);
        }

        if (isset($filters['folder_id'])) {
            $notesQuery->where('folder_id', $filters['folder_id']);
        }

        if (isset($filters['tags'])) {
            $notesQuery->whereHas('tags', function($q) use ($filters) {
                $q->whereIn('tags.id', $filters['tags']);
            });
        }

        if (isset($filters['status'])) {
            $notesQuery->where('status', $filters['status']);
        }

        // Smart search: multiple strategies
        if (!empty($query)) {
            $notesQuery->where(function($q) use ($query) {
                // Strategy 1: Full-text search on title and content
                $q->where(function($subQ) use ($query) {
                    $subQ->where('title', 'like', "%{$query}%")
                         ->orWhere('content', 'like', "%{$query}%")
                         ->orWhere('summary', 'like', "%{$query}%");
                });

                // Strategy 2: Tag matching
                $q->orWhereHas('tags', function($tagQ) use ($query) {
                    $tagQ->where('name', 'like', "%{$query}%");
                });

                // Strategy 3: Word-by-word search (for multi-word queries)
                $words = explode(' ', $query);
                if (count($words) > 1) {
                    foreach ($words as $word) {
                        $word = trim($word);
                        if (strlen($word) >= 2) {
                            $q->orWhere(function($wordQ) use ($word) {
                                $wordQ->where('title', 'like', "%{$word}%")
                                      ->orWhere('content', 'like', "%{$word}%");
                            });
                        }
                    }
                }
            });
        }

        // Semantic search for premium users (using embeddings)
        if ($user && $user->hasPremium() && !empty($query)) {
            try {
                $semanticResults = $this->embeddingService->semanticSearch($query, $user, 20);
                if ($semanticResults->isNotEmpty()) {
                    $semanticIds = $semanticResults->pluck('id')->toArray();
                    $notesQuery->orderByRaw('FIELD(id, ' . implode(',', array_map(fn($id) => "'{$id}'", $semanticIds)) . ')');
                }
            } catch (\Exception $e) {
                // Fallback to regular search if semantic search fails
                logger()->warning('Semantic search failed, using fallback', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Relevance scoring (premium feature)
        if ($user && $user->hasPremium()) {
            $notesQuery->selectRaw('notes.*, 
                (
                    CASE 
                        WHEN title LIKE ? THEN 10
                        WHEN title LIKE ? THEN 5
                        WHEN content LIKE ? THEN 3
                        WHEN summary LIKE ? THEN 2
                        ELSE 1
                    END
                ) as relevance_score',
                [
                    "%{$query}%",           // Exact match in title
                    "%{$query}%",           // Partial match in title
                    "%{$query}%",           // Match in content
                    "%{$query}%",           // Match in summary
                ]
            )->orderByDesc('relevance_score');
        }

        // Default ordering
        $notesQuery->latest();

        return $notesQuery->with(['user', 'tags', 'workspace', 'folder'])
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();
    }

    /**
     * Search notes by semantic similarity (premium feature).
     * Uses keyword matching and tag relationships.
     */
    public function semanticSearch(string $query, ?User $user = null, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        $query = trim($query);
        
        // Extract keywords from query
        $keywords = $this->extractKeywords($query);
        
        $notesQuery = Note::query();

        if ($user) {
            $notesQuery->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('is_public', true);
            });
        } else {
            $notesQuery->where('is_public', true);
        }

        // Find related tags
        $relatedTags = Tag::where(function($q) use ($keywords) {
            foreach ($keywords as $keyword) {
                $q->orWhere('name', 'like', "%{$keyword}%");
            }
        })->pluck('id');

        // Search notes with semantic matching
        $notesQuery->where(function($q) use ($keywords, $relatedTags) {
            // Match keywords in title/content
            foreach ($keywords as $keyword) {
                $q->orWhere('title', 'like', "%{$keyword}%")
                  ->orWhere('content', 'like', "%{$keyword}%");
            }

            // Match related tags
            if ($relatedTags->isNotEmpty()) {
                $q->orWhereHas('tags', function($tagQ) use ($relatedTags) {
                    $tagQ->whereIn('tags.id', $relatedTags);
                });
            }
        });

        return $notesQuery->with(['user', 'tags'])
            ->limit($limit)
            ->get();
    }

    /**
     * Extract keywords from query.
     */
    protected function extractKeywords(string $query): array
    {
        $query = mb_strtolower($query, 'UTF-8');
        preg_match_all('/\b[a-z]{3,}\b/i', $query, $matches);
        return array_unique($matches[0] ?? []);
    }

    /**
     * Get search suggestions based on query.
     */
    public function getSuggestions(string $query, ?User $user = null, int $limit = 5): array
    {
        $query = trim($query);
        if (strlen($query) < 2) {
            return [];
        }

        $suggestions = [];

        // Suggest tags
        $tags = Tag::where('name', 'like', "%{$query}%")
            ->limit($limit)
            ->pluck('name')
            ->toArray();
        $suggestions = array_merge($suggestions, $tags);

        // Suggest note titles
        $notesQuery = Note::query();
        if ($user) {
            $notesQuery->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('is_public', true);
            });
        } else {
            $notesQuery->where('is_public', true);
        }

        $titles = $notesQuery->where('title', 'like', "%{$query}%")
            ->limit($limit)
            ->pluck('title')
            ->toArray();
        $suggestions = array_merge($suggestions, $titles);

        return array_unique(array_slice($suggestions, 0, $limit));
    }
}

