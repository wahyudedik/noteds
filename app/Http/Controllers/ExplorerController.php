<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class ExplorerController extends Controller
{
    /**
     * Display a listing of articles.
     */
    public function index(Request $request)
    {
        try {
            $cacheKey = 'explorer_articles_' . md5($request->getQueryString());
            $cacheTTL = config('articles.cache.articles_list_ttl', 900);

            $articles = Cache::remember($cacheKey, $cacheTTL, function () use ($request) {
                $query = Article::query()->recent();

                // Apply category filter
                if ($request->has('category') && $request->category) {
                    $query->byCategory($request->category);
                }

                // Apply search filter
                if ($request->has('search') && $request->search) {
                    $query->search($request->search);
                }

                return $query->paginate(20)->withQueryString();
            });

            // Get available categories (cached)
            $categories = Cache::remember('explorer_categories', config('articles.cache.categories_ttl', 3600), function () {
                return Article::distinct('category')
                    ->whereNotNull('category')
                    ->pluck('category')
                    ->sort()
                    ->values();
            });

            return Inertia::render('Explorer/Index', [
                'articles' => $articles,
                'filters' => [
                    'search' => $request->search,
                    'category' => $request->category,
                ],
                'categories' => $categories,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Explorer index error: ' . $e->getMessage());
            
            return Inertia::render('Explorer/Index', [
                'articles' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20),
                'filters' => [
                    'search' => $request->search,
                    'category' => $request->category,
                ],
                'categories' => [],
            ]);
        }
    }

    /**
     * Search articles.
     */
    public function search(Request $request)
    {
        try {
            $request->validate([
                'q' => 'required|string|min:1|max:255',
            ]);

            $query = Article::query()
                ->search($request->q)
                ->recent();

            // Apply category filter if provided
            if ($request->has('category') && $request->category) {
                $query->byCategory($request->category);
            }

            $articles = $query->paginate(20)->withQueryString();

            // Get available categories
            $categories = Article::distinct('category')
                ->whereNotNull('category')
                ->pluck('category')
                ->sort()
                ->values();

            return Inertia::render('Explorer/Index', [
                'articles' => $articles,
                'filters' => [
                    'search' => $request->q,
                    'category' => $request->category,
                ],
                'categories' => $categories,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Explorer search error: ' . $e->getMessage());
            
            return back()->withErrors([
                'search' => 'Terjadi kesalahan saat mencari artikel. Silakan coba lagi.',
            ]);
        }
    }
}
