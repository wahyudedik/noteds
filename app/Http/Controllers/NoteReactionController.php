<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\NoteReaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteReactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store a new reaction or toggle existing.
     */
    public function toggle(Request $request, Note $note): JsonResponse
    {
        $validated = $request->validate([
            'reaction_type' => ['required', 'in:like,love,helpful,insightful,thanks'],
        ]);

        $reaction = NoteReaction::where('note_id', $note->id)
            ->where('user_id', auth()->id())
            ->where('reaction_type', $validated['reaction_type'])
            ->first();

        if ($reaction) {
            // Remove reaction
            $reaction->delete();
            $action = 'removed';
        } else {
            // Remove other reactions from same user
            NoteReaction::where('note_id', $note->id)
                ->where('user_id', auth()->id())
                ->delete();

            // Add new reaction
            NoteReaction::create([
                'note_id' => $note->id,
                'user_id' => auth()->id(),
                'reaction_type' => $validated['reaction_type'],
            ]);
            $action = 'added';
        }

        // Get updated reaction counts
        $reactions = NoteReaction::where('note_id', $note->id)
            ->selectRaw('reaction_type, COUNT(*) as count')
            ->groupBy('reaction_type')
            ->pluck('count', 'reaction_type')
            ->toArray();

        return response()->json([
            'success' => true,
            'action' => $action,
            'reaction_type' => $validated['reaction_type'],
            'reactions' => $reactions,
            'user_reaction' => $action === 'added' ? $validated['reaction_type'] : null,
        ]);
    }

    /**
     * Store a new reaction.
     */
    public function store(Request $request, Note $note): JsonResponse
    {
        $validated = $request->validate([
            'reaction_type' => ['required', 'in:like,love,helpful,insightful,thanks'],
        ]);

        // Check if user already has a reaction
        $existing = NoteReaction::where('note_id', $note->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            // Update existing reaction
            $existing->update(['reaction_type' => $validated['reaction_type']]);
        } else {
            // Create new reaction
            NoteReaction::create([
                'note_id' => $note->id,
                'user_id' => auth()->id(),
                'reaction_type' => $validated['reaction_type'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reaction added successfully.',
        ]);
    }

    /**
     * Remove the specified reaction.
     */
    public function destroy(Note $note, NoteReaction $reaction): JsonResponse
    {
        // Ensure user owns this reaction
        if ($reaction->user_id !== auth()->id()) {
            abort(403);
        }

        $reaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reaction removed successfully.',
        ]);
    }
}
