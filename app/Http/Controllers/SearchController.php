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
        ]);

        $query = $request->input('q');
        $type = $request->input('type', 'all');
        
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
        ];

        $results = $this->searchService->search($query, $filters, 15);

        // Use PostSearchService for advanced post filtering if type is posts or all
        if ($type === 'posts' || $type === 'all') {
            $postFilters = array_merge($filters, [
                'purpose_type' => $filters['purpose_type'] === 'all' ? null : $filters['purpose_type'],
            ]);
            $results['posts'] = $this->postSearchService->search($query, $postFilters, 15);
        }

        return Inertia::render('Search/Index', [
            'query' => $query,
            'results' => $results,
            'filters' => $filters,
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

        $suggestions = $this->searchService->getSuggestions($request->input('q'), 5);

        return response()->json([
            'suggestions' => $suggestions,
        ]);
    }
}

