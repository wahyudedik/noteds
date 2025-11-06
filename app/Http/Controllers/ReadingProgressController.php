<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\ReadingProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReadingProgressController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'premium']);
    }

    /**
     * Update reading progress for a note.
     */
    public function update(Request $request, Note $note): JsonResponse
    {
        // Check if user has purchased this note (for paid notes)
        if ($note->price > 0 && !auth()->user()->hasPurchasedNote($note->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus membeli note ini terlebih dahulu.'
            ], 403);
        }

        $validated = $request->validate([
            'progress_percentage' => 'required|integer|min:0|max:100',
            'last_position' => 'nullable|integer|min:0',
            'read_characters' => 'nullable|integer|min:0',
            'total_characters' => 'nullable|integer|min:0',
        ]);

        $user = auth()->user();

        // Get or create reading progress
        $progress = ReadingProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'note_id' => $note->id,
            ],
            [
                'progress_percentage' => 0,
                'last_position' => 0,
                'total_characters' => $validated['total_characters'] ?? strlen(strip_tags($note->content)),
                'read_characters' => 0,
                'started_at' => now(),
            ]
        );

        // Update progress
        $progress->update([
            'progress_percentage' => $validated['progress_percentage'],
            'last_position' => $validated['last_position'] ?? $progress->last_position,
            'read_characters' => $validated['read_characters'] ?? $progress->read_characters,
            'total_characters' => $validated['total_characters'] ?? $progress->total_characters,
            'last_read_at' => now(),
            'completed_at' => $validated['progress_percentage'] >= 100 ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'progress' => $progress->fresh(),
        ]);
    }

    /**
     * Get reading progress for a note.
     */
    public function show(Note $note): JsonResponse
    {
        $progress = ReadingProgress::where('user_id', auth()->id())
            ->where('note_id', $note->id)
            ->first();

        return response()->json([
            'success' => true,
            'progress' => $progress,
        ]);
    }
}

