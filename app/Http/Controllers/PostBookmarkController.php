<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PostBookmarkController extends Controller
{
    /**
     * Display user's bookmarked posts.
     */
    public function index(): View
    {
        $bookmarkedPosts = auth()->user()
            ->bookmarkedPosts()
            ->published()
            ->visibleTo(auth()->user())
            ->with(['user', 'note.user', 'likes', 'media', 'hashtags'])
            ->withCount(['replies', 'allComments'])
            ->orderBy('is_pinned', 'desc')
            ->latest()
            ->paginate(15);

        return view('forum.bookmarks', compact('bookmarkedPosts'));
    }

    /**
     * Bookmark or unbookmark a post.
     */
    public function toggle(Post $post): JsonResponse|RedirectResponse
    {
        $user = auth()->user();

        if (!$post->canBeViewedBy($user)) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This post is not available.',
                ], 403);
            }

            return redirect()->back()->with('error', 'This post is not available.');
        }

        $isBookmarked = $user->hasBookmarked($post);

        if ($isBookmarked) {
            $user->bookmarkedPosts()->detach($post->id);
            $message = 'Post removed from bookmarks.';
            $bookmarked = false;
        } else {
            $user->bookmarkedPosts()->attach($post->id);
            $message = 'Post bookmarked successfully.';
            $bookmarked = true;
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'bookmarked' => $bookmarked,
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }
}

