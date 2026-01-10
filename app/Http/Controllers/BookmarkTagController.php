<?php

namespace App\Http\Controllers;

use App\Models\BookmarkTag;
use App\Services\BookmarkTagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookmarkTagController extends Controller
{
    public function __construct(
        private BookmarkTagService $tagService
    ) {}

    /**
     * List tags (user's + global).
     */
    public function index(Request $request): Response|JsonResponse
    {
        $user = $request->user();

        $tags = BookmarkTag::forUser($user)
            ->orderBy('usage_count', 'desc')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'tags' => $tags,
            ]);
        }

        return Inertia::render('Bookmarks/Tags', [
            'tags' => $tags,
        ]);
    }

    /**
     * Create tag.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_global' => 'boolean',
        ]);

        $user = $request->user();
        $tag = $this->tagService->createOrGetTag(
            $request->name,
            $user,
            $request->boolean('is_global', false)
        );

        return response()->json([
            'message' => 'Tag created successfully.',
            'tag' => $tag,
        ]);
    }

    /**
     * Update tag.
     */
    public function update(Request $request, BookmarkTag $tag): JsonResponse
    {
        $this->authorize('update', $tag);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $tag->update([
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name),
        ]);

        return response()->json([
            'message' => 'Tag updated successfully.',
            'tag' => $tag,
        ]);
    }

    /**
     * Delete tag.
     */
    public function destroy(BookmarkTag $tag): JsonResponse
    {
        $this->authorize('delete', $tag);

        $tag->delete();

        return response()->json([
            'message' => 'Tag deleted successfully.',
        ]);
    }

    /**
     * Toggle global/private.
     */
    public function toggleGlobal(Request $request, BookmarkTag $tag): JsonResponse
    {
        $this->authorize('toggleGlobal', $tag);

        if ($tag->is_global) {
            $tag->makePrivate($request->user());
        } else {
            $tag->makeGlobal();
        }

        return response()->json([
            'message' => 'Tag status updated successfully.',
            'tag' => $tag,
        ]);
    }

    /**
     * Get tag suggestions (autocomplete).
     */
    public function suggestions(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:1',
        ]);

        $user = $request->user();
        $suggestions = $this->tagService->getTagSuggestions($request->q, $user);

        return response()->json([
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Show tag with bookmarks.
     */
    public function show(Request $request, BookmarkTag $tag): Response
    {
        $this->authorize('view', $tag);

        $bookmarks = $tag->bookmarks()
            ->where('user_id', $request->user()->id)
            ->with('post.user')
            ->latest()
            ->paginate(20);

        return Inertia::render('Bookmarks/TagShow', [
            'tag' => $tag,
            'bookmarks' => $bookmarks,
        ]);
    }
}
