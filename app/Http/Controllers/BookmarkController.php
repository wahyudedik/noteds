<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Bookmark;
use App\Models\BookmarkTag;
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
        $user = $request->user();
        $selectedCollectionId = $request->query('collection');
        $selectedTagId = $request->query('tag');

        $query = Bookmark::where('user_id', $user->id)
            ->with(['post.user', 'collection', 'tags']);

        if ($selectedCollectionId) {
            $query->where('collection_id', $selectedCollectionId);
        }

        if ($selectedTagId) {
            $query->whereHas('tags', function ($q) use ($selectedTagId) {
                $q->where('bookmark_tags.id', $selectedTagId);
            });
        }

        $bookmarks = $query->latest()->paginate(20);

        // Get collections tree
        $collectionService = app(\App\Services\BookmarkCollectionService::class);
        $collections = $collectionService->getCollectionTree($user);

        // Get tags
        $userTags = BookmarkTag::where('user_id', $user->id)
            ->orderBy('usage_count', 'desc')
            ->get();
        $globalTags = BookmarkTag::where('is_global', true)
            ->orderBy('usage_count', 'desc')
            ->limit(20)
            ->get();

        return Inertia::render('Bookmarks/Index', [
            'bookmarks' => $bookmarks,
            'collections' => $collections,
            'selectedCollectionId' => $selectedCollectionId,
            'selectedTagId' => $selectedTagId,
            'userTags' => $userTags,
            'globalTags' => $globalTags,
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
        // Note: BookmarkPolicy::create() expects (User $user, Post $post)
        // Laravel automatically passes authenticated user as first param
        $this->authorize('create', $post);

        $user = $request->user();

        // Validate collection ownership with explicit validation rule
        $request->validate([
            'collection_id' => [
                'nullable',
                'exists:bookmark_collections,id',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value) {
                        $collection = \App\Models\BookmarkCollection::find($value);
                        if (!$collection || $collection->user_id !== $user->id) {
                            $fail('The selected collection does not belong to you.');
                        }
                    }
                },
            ],
        ]);

        $collectionId = $request->collection_id;

        // Additional safety check (should not be needed due to validation, but keeping for safety)
        if ($collectionId) {
            $collection = \App\Models\BookmarkCollection::where('id', $collectionId)
                ->where('user_id', $user->id)
                ->firstOrFail();
        }

        // Check if already bookmarked in this collection
        $existingBookmark = Bookmark::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->where('collection_id', $collectionId)
            ->first();

        if ($existingBookmark) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['message' => 'Post is already bookmarked in this collection.'], 200);
            }
            return back()->with('info', 'Post is already bookmarked in this collection.');
        }

        Bookmark::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'collection_id' => $collectionId,
        ]);

        // Return updated bookmark status for Inertia
        if ($request->header('X-Inertia')) {
            $userBookmarks = [];
            
            // If Inertia partial data is requested for userBookmarks, we need to provide updated status
            // Since we're using back(), we can only update the current post's bookmark status
            // The full page load will refresh all bookmarks properly via PostController
            if ($request->header('X-Inertia-Partial-Data') && str_contains($request->header('X-Inertia-Partial-Data'), 'userBookmarks')) {
                // Only set the current post's bookmark status to true (just bookmarked)
                $userBookmarks[$post->id] = true;
                
                // Note: We can't efficiently get all posts on the current page since we're using back()
                // The PostController will handle loading all bookmarks on the next full page load
                // For partial updates, we just mark the current post
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
        $bookmark = Bookmark::where('user_id', $request->user()->id)
            ->where('post_id', $post->id)
            ->first();

        if (!$bookmark) {
            return back()->withErrors(['bookmark' => 'Bookmark not found.']);
        }

        $this->authorize('delete', $bookmark);

        $bookmark->delete();

        // Return updated bookmark status for Inertia
        if ($request->header('X-Inertia')) {
            $userBookmarks = [];
            
            // If Inertia partial data is requested for userBookmarks, update the current post's status
            if ($request->header('X-Inertia-Partial-Data') && str_contains($request->header('X-Inertia-Partial-Data'), 'userBookmarks')) {
                // Only set the current post's bookmark status to false (just removed)
                $userBookmarks[$post->id] = false;
                
                // Note: We can't efficiently get all posts on the current page since we're using back()
                // The PostController will handle loading all bookmarks on the next full page load
            }

            return back()->with([
                'success' => 'Bookmark removed successfully.',
                'userBookmarks' => $userBookmarks,
            ]);
        }

        return back()->with('success', 'Bookmark removed successfully.');
    }

    /**
     * Update bookmark notes.
     */
    public function updateNotes(Request $request, Bookmark $bookmark)
    {
        $this->authorize('updateNotes', $bookmark);

        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $bookmark->update([
            'notes' => $request->notes,
            'notes_updated_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Notes updated successfully.',
                'notes' => $bookmark->notes,
            ]);
        }

        return back()->with('success', 'Notes updated successfully.');
    }

    /**
     * Get bookmark notes.
     */
    public function getNotes(Request $request, Bookmark $bookmark)
    {
        $this->authorize('view', $bookmark);

        return response()->json([
            'notes' => $bookmark->notes,
            'notes_updated_at' => $bookmark->notes_updated_at,
        ]);
    }
}

