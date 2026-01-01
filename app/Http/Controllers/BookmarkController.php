<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Bookmark;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookmarkController extends Controller
{
    /**
     * Display a listing of bookmarked posts.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $bookmarks = Bookmark::where('user_id', $request->user()->id)
            ->with('post.user')
            ->latest()
            ->paginate(20);

        return Inertia::render('Bookmarks/Index', [
            'bookmarks' => $bookmarks,
        ]);
    }

    /**
     * Bookmark a post.
     *
     * @param Request $request
     * @param Post $post
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Post $post)
    {
        // Check if already bookmarked
        $existingBookmark = Bookmark::where('user_id', $request->user()->id)
            ->where('post_id', $post->id)
            ->first();

        if ($existingBookmark) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['message' => 'Post is already bookmarked.'], 200);
            }
            return back()->with('info', 'Post is already bookmarked.');
        }

        Bookmark::create([
            'user_id' => $request->user()->id,
            'post_id' => $post->id,
        ]);

        // Return updated bookmark status for Inertia
        if ($request->header('X-Inertia')) {
            // Get all user bookmarks for the current page
            $userBookmarks = [];
            if ($request->header('X-Inertia-Partial-Data') && str_contains($request->header('X-Inertia-Partial-Data'), 'userBookmarks')) {
                // Get bookmarks for posts that might be on current page
                $bookmarks = Bookmark::where('user_id', $request->user()->id)
                    ->pluck('post_id')
                    ->toArray();
                $userBookmarks[$post->id] = true;
            }

            return back()->with([
                'success' => 'Post bookmarked successfully.',
                'userBookmarks' => $userBookmarks,
            ]);
        }

        return back()->with('success', 'Post bookmarked successfully.');
    }

    /**
     * Remove bookmark from a post.
     *
     * @param Request $request
     * @param Post $post
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Post $post)
    {
        Bookmark::where('user_id', $request->user()->id)
            ->where('post_id', $post->id)
            ->delete();

        // Return updated bookmark status for Inertia
        if ($request->header('X-Inertia')) {
            // Get all user bookmarks for the current page
            $userBookmarks = [];
            if ($request->header('X-Inertia-Partial-Data') && str_contains($request->header('X-Inertia-Partial-Data'), 'userBookmarks')) {
                $userBookmarks[$post->id] = false;
            }

            return back()->with([
                'success' => 'Bookmark removed successfully.',
                'userBookmarks' => $userBookmarks,
            ]);
        }

        return back()->with('success', 'Bookmark removed successfully.');
    }
}

