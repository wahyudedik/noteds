<?php

namespace App\Http\Controllers;

use App\Models\BookmarkCollection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookmarkCollectionPublicController extends Controller
{
    /**
     * View public collection.
     */
    public function show(Request $request, string $slug): Response
    {
        $collection = BookmarkCollection::where('public_slug', $slug)
            ->where('is_public', true)
            ->with(['user', 'bookmarks.post.user'])
            ->firstOrFail();

        // Check if user has edit access
        $canEdit = false;
        if ($request->user()) {
            $canEdit = $collection->canUserEdit($request->user());
        }

        return Inertia::render('Bookmarks/Public', [
            'collection' => $collection,
            'canEdit' => $canEdit,
        ]);
    }

    /**
     * List public collections (discovery).
     */
    public function index(Request $request): Response
    {
        $collections = BookmarkCollection::where('is_public', true)
            ->with('user')
            ->withCount('bookmarks')
            ->latest()
            ->paginate(20);

        return Inertia::render('Bookmarks/PublicIndex', [
            'collections' => $collections,
        ]);
    }
}
