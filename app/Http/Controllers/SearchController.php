<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use App\Services\PostSearchService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function __construct(
        private SearchService $searchService,
        private PostSearchService $postSearchService
    ) {}

    /**
     * Display search results.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $request->validate([
            'q' => 'required|string|min:1|max:255',
            'type' => 'nullable|in:all,posts,users,products,articles',
            'date' => 'nullable|in:today,week,month,year',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'category' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'hashtags' => 'nullable|string|max:500',
            'purpose_type' => 'nullable|in:all,idea_business,ask_question,share_experience,find_partner,find_tools,validate_idea',
            'min_engagement' => 'nullable|integer|min:0',
            'sort_by' => 'nullable|in:latest,oldest,trending,most_engaged,most_upvoted,most_commented',
            'publish_status' => 'nullable|in:draft,published',
            'hashtags_match' => 'nullable|in:any,all',
        ]);

        $query = $request->input('q');
        $type = $request->input('type', 'all');
        if ($type === 'products') {
            $type = 'all';
        }
        
        $filters = [
            'type' => $type,
            'date' => $request->input('date'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'category' => $request->input('category'),
            'author' => $request->input('author'),
            'hashtags' => $request->input('hashtags'),
            'purpose_type' => $request->input('purpose_type', 'all'),
            'min_engagement' => $request->input('min_engagement'),
            'sort_by' => $request->input('sort_by', 'latest'),
            'publish_status' => $request->input('publish_status'),
            'hashtags_match' => $request->input('hashtags_match', 'any'),
        ];

        $start = microtime(true);
        $perPageOptions = [10, 20, 50, 100];
        $defaultPerPage = 20;
        $postsPer = in_array((int)$request->input('posts_per_page'), $perPageOptions) ? (int)$request->input('posts_per_page') : $defaultPerPage;
        $usersPer = in_array((int)$request->input('users_per_page'), $perPageOptions) ? (int)$request->input('users_per_page') : $defaultPerPage;
        $articlesPer = in_array((int)$request->input('articles_per_page'), $perPageOptions) ? (int)$request->input('articles_per_page') : $defaultPerPage;

        $postsPage = max(1, (int) $request->input('posts_page', 1));
        $usersPage = max(1, (int) $request->input('users_page', 1));
        $articlesPage = max(1, (int) $request->input('articles_page', 1));

        $results = [
            'posts' => null,
            'users' => null,
            'articles' => null,
        ];
        // Users
        if ($type === 'all' || $type === 'users') {
            $results['users'] = $this->searchService->searchUsers($query, $usersPer)
                ->setPageName('users_page')
                ->appends(['users_per_page' => $usersPer]);
        }
        // Articles
        if ($type === 'all' || $type === 'articles') {
            $results['articles'] = $this->searchService->searchArticles($query, $filters['category'] ?? null, $articlesPer)
                ->setPageName('articles_page')
                ->appends(['articles_per_page' => $articlesPer]);
        }

        // Use PostSearchService for advanced post filtering if type is posts or all
        if ($type === 'posts' || $type === 'all') {
            $postFilters = array_merge($filters, [
                'purpose_type' => $filters['purpose_type'] === 'all' ? null : $filters['purpose_type'],
            ]);
            $postsPaginator = $this->postSearchService->search($query, $postFilters, $postsPer)
                ->setPageName('posts_page')
                ->appends(['posts_per_page' => $postsPer]);
            $results['posts'] = $postsPaginator;
        }

        $durationMs = (int) round((microtime(true) - $start) * 1000);
        $hasResults = collect($results)->filter(function ($p) {
            return $p && $p->total() > 0;
        })->isNotEmpty();

        if ($request->user()) {
            \App\Models\SearchHistory::create([
                'user_id' => $request->user()->id,
                'query' => $query,
                'filters' => $filters,
                'zero_result' => !$hasResults,
                'duration_ms' => $durationMs,
            ]);
            $count = \App\Models\SearchHistory::where('user_id', $request->user()->id)->count();
            if ($count > 100) {
                \App\Models\SearchHistory::where('user_id', $request->user()->id)
                    ->orderBy('created_at', 'asc')
                    ->limit($count - 100)
                    ->delete();
            }
        }

        return Inertia::render('Search/Index', [
            'query' => $query,
            'results' => $results,
            'filters' => $filters,
            'pagination' => [
                'posts' => $results['posts'] ? ['current_page' => $results['posts']->currentPage(), 'last_page' => $results['posts']->lastPage(), 'per_page' => $results['posts']->perPage(), 'total' => $results['posts']->total()] : null,
                'users' => $results['users'] ? ['current_page' => $results['users']->currentPage(), 'last_page' => $results['users']->lastPage(), 'per_page' => $results['users']->perPage(), 'total' => $results['users']->total()] : null,
                'articles' => $results['articles'] ? ['current_page' => $results['articles']->currentPage(), 'last_page' => $results['articles']->lastPage(), 'per_page' => $results['articles']->perPage(), 'total' => $results['articles']->total()] : null,
                'per_page_options' => [10, 20, 50, 100],
            ],
            'savedSearches' => $request->user() ? \App\Models\SavedSearch::where('user_id', $request->user()->id)->orderBy('created_at', 'desc')->limit(10)->get() : [],
            'history' => $request->user() ? \App\Models\SearchHistory::where('user_id', $request->user()->id)->orderBy('created_at', 'desc')->limit(10)->get(['id','query','filters','created_at']) : [],
        ]);
    }

    /**
     * Get search suggestions/autocomplete.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggestions(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:1|max:255',
        ]);

        $suggestions = $this->searchService->getSuggestions($request->input('q'), 10, $request->user());

        return response()->json([
            'suggestions' => $suggestions,
        ]);
    }

    public function quick(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:1|max:255',
        ]);

        $results = $this->searchService->quick($request->input('q'), 5);

        return response()->json($results);
    }

    public function saved(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'q' => 'nullable|string|max:255',
            'filters' => 'nullable|array',
        ]);
        $saved = \App\Models\SavedSearch::create([
            'user_id' => $request->user()->id,
            'name' => $request->input('name'),
            'query' => $request->input('q'),
            'filters' => $request->input('filters', []),
        ]);
        return response()->json(['data' => $saved], 201);
    }

    public function listSaved(Request $request)
    {
        $saved = \App\Models\SavedSearch::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json(['data' => $saved]);
    }

    public function deleteSaved(\App\Models\SavedSearch $savedSearch, Request $request)
    {
        if ($savedSearch->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }
        $savedSearch->delete();
        return response()->json(['status' => 'ok']);
    }

    public function history(Request $request)
    {
        $history = \App\Models\SearchHistory::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get(['id', 'query', 'filters', 'created_at']);
        return response()->json(['data' => $history]);
    }

    public function deleteHistoryItem(\App\Models\SearchHistory $historyItem, Request $request)
    {
        if ($historyItem->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }
        $historyItem->delete();
        return response()->json(['status' => 'ok']);
    }
}
