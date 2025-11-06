<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'premium']);
    }

    /**
     * Get all bookmarks for a note.
     */
    public function index(Note $note): JsonResponse
    {
        // Check if user has purchased this note (for paid notes)
        if ($note->price > 0 && !auth()->user()->hasPurchasedNote($note->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus membeli note ini terlebih dahulu.'
            ], 403);
        }

        $bookmarks = Bookmark::where('user_id', auth()->id())
            ->where('note_id', $note->id)
            ->orderBy('order')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'bookmarks' => $bookmarks,
        ]);
    }

    /**
     * Create a new bookmark.
     */
    public function store(Request $request, Note $note): JsonResponse
    {
        // Check if user has purchased this note (for paid notes)
        if ($note->price > 0 && !auth()->user()->hasPurchasedNote($note->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus membeli note ini terlebih dahulu.'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'note_text' => 'nullable|string|max:1000',
            'section_id' => 'nullable|string|max:255',
            'section_text' => 'nullable|string',
            'position' => 'required|integer|min:0',
        ]);

        $maxOrder = Bookmark::where('user_id', auth()->id())
            ->where('note_id', $note->id)
            ->max('order') ?? 0;

        $bookmark = Bookmark::create([
            'user_id' => auth()->id(),
            'note_id' => $note->id,
            'title' => $validated['title'] ?? 'Bookmark',
            'note_text' => $validated['note_text'] ?? null,
            'section_id' => $validated['section_id'] ?? null,
            'section_text' => $validated['section_text'] ?? null,
            'position' => $validated['position'],
            'order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'bookmark' => $bookmark,
        ]);
    }

    /**
     * Update a bookmark.
     */
    public function update(Request $request, Bookmark $bookmark): JsonResponse
    {
        // Check ownership
        if ($bookmark->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'note_text' => 'nullable|string|max:1000',
        ]);

        $bookmark->update($validated);

        return response()->json([
            'success' => true,
            'bookmark' => $bookmark->fresh(),
        ]);
    }

    /**
     * Delete a bookmark.
     */
    public function destroy(Bookmark $bookmark): JsonResponse|RedirectResponse
    {
        // Check ownership
        if ($bookmark->user_id !== auth()->id()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.'
                ], 403);
            }
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $bookmark->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bookmark deleted successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Bookmark deleted successfully.');
    }
}

