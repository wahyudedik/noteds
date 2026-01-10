<?php

namespace App\Http\Controllers;

use App\Models\Hashtag;
use App\Services\HashtagService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HashtagController extends Controller
{
    public function __construct(
        private HashtagService $hashtagService
    ) {}

    /**
     * Show hashtag detail page with posts.
     */
    public function show(Request $request, string $hashtag): Response
    {
        $hashtagModel = Hashtag::where('slug', $hashtag)->firstOrFail();

        $posts = $hashtagModel->posts()
            ->with(['user', 'media', 'hashtags'])
            ->where('status', 'active')
            ->latest()
            ->paginate(15);

        return Inertia::render('Hashtags/Show', [
            'hashtag' => $hashtagModel,
            'posts' => $posts,
        ]);
    }

    /**
     * Get hashtag suggestions for autocomplete.
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $hashtags = Hashtag::where('name', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->orderBy('posts_count', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'slug', 'posts_count']);

        return response()->json($hashtags);
    }
}
