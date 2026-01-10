<?php

namespace App\Http\Controllers;

use App\Models\BookmarkCollection;
use App\Policies\BookmarkCollectionPolicy;
use App\Services\BookmarkCollectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookmarkCollectionController extends Controller
{
    public function __construct(
        private BookmarkCollectionService $collectionService
    ) {}

    /**
     * List user collections (tree structure).
     */
    public function index(Request $request): Response|JsonResponse
    {
        $user = $request->user();
        $tree = $this->collectionService->getCollectionTree($user);

        if ($request->wantsJson()) {
            return response()->json([
                'collections' => $tree,
            ]);
        }

        return Inertia::render('Bookmarks/Collections', [
            'collections' => $tree,
        ]);
    }

    /**
     * Create new collection.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:bookmark_collections,id',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
        ]);

        $user = $request->user();

        // Validate parent belongs to user
        if ($request->parent_id) {
            $parent = BookmarkCollection::where('id', $request->parent_id)
                ->where('user_id', $user->id)
                ->firstOrFail();
        }

        $collection = BookmarkCollection::create([
            'user_id' => $user->id,
            'parent_id' => $request->parent_id,
            'name' => $request->name,
            'description' => $request->description,
            'icon' => $request->icon,
            'color' => $request->color,
            'sort_order' => BookmarkCollection::where('user_id', $user->id)
                ->where('parent_id', $request->parent_id)
                ->max('sort_order') + 1,
        ]);

        return response()->json([
            'message' => 'Collection created successfully.',
            'collection' => $collection,
        ]);
    }

    /**
     * Update collection.
     */
    public function update(Request $request, BookmarkCollection $collection): JsonResponse
    {
        $this->authorize('update', $collection);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
        ]);

        $collection->update($request->only(['name', 'description', 'icon', 'color']));

        return response()->json([
            'message' => 'Collection updated successfully.',
            'collection' => $collection,
        ]);
    }

    /**
     * Delete collection.
     */
    public function destroy(BookmarkCollection $collection): JsonResponse
    {
        $this->authorize('delete', $collection);

        try {
            $this->collectionService->deleteCollection($collection);
            return response()->json([
                'message' => 'Collection deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reorder collections.
     */
    public function reorder(Request $request, BookmarkCollection $collection): JsonResponse
    {
        $this->authorize('update', $collection);

        $request->validate([
            'sort_order' => 'required|integer|min:0',
        ]);

        $collection->update(['sort_order' => $request->sort_order]);

        return response()->json([
            'message' => 'Collection reordered successfully.',
        ]);
    }

    /**
     * Move collection to different parent.
     */
    public function move(Request $request, BookmarkCollection $collection): JsonResponse
    {
        $this->authorize('update', $collection);

        $request->validate([
            'parent_id' => 'nullable|exists:bookmark_collections,id',
        ]);

        $newParent = $request->parent_id 
            ? BookmarkCollection::where('id', $request->parent_id)
                ->where('user_id', $request->user()->id)
                ->firstOrFail()
            : null;

        try {
            $this->collectionService->moveCollection($collection, $newParent);
            return response()->json([
                'message' => 'Collection moved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
