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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Post $post)
    {
        // Check if already bookmarked
        $existingBookmark = Bookmark::where('user_id', $request->user()->id)
            ->where('post_id', $post->id)
            ->first();

        if ($existingBookmark) {
            return back()->with('info', 'Post is already bookmarked.');
        }

        Bookmark::create([
            'user_id' => $request->user()->id,
            'post_id' => $post->id,
        ]);

        return back()->with('success', 'Post bookmarked successfully.');
    }

    /**
     * Remove bookmark from a post.
     *
     * @param Request $request
     * @param Post $post
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, Post $post)
    {
        Bookmark::where('user_id', $request->user()->id)
            ->where('post_id', $post->id)
            ->delete();

        return back()->with('success', 'Bookmark removed successfully.');
    }
}

