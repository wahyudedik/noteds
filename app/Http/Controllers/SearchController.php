<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function __construct(
        private SearchService $searchService
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
            'category' => 'nullable|string|max:255',
        ]);

        $query = $request->input('q');
        $filters = [
            'type' => $request->input('type', 'all'),
            'date' => $request->input('date'),
            'category' => $request->input('category'),
        ];

        $results = $this->searchService->search($query, $filters, 15);

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

