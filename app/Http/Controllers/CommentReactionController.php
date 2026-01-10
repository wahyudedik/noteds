<?php

namespace App\Http\Controllers;

use App\Constants\CommentReactions;
use App\Models\Comment;
use App\Models\CommentReaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentReactionController extends Controller
{
    /**
     * Add or remove a reaction to a comment.
     */
    public function react(Request $request, Comment $comment): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'emoji' => ['required', 'string', 'max:10'],
        ]);

        $emoji = $validated['emoji'];

        // Validate emoji is allowed
        if (!CommentReactions::isAllowed($emoji)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Emoji not allowed.',
                ], 422);
            }
            return back()->withErrors(['emoji' => 'Emoji not allowed.']);
        }

        try {
            DB::transaction(function () use ($comment, $emoji) {
                $reaction = CommentReaction::firstOrCreate(
                    [
                        'comment_id' => $comment->id,
                        'emoji' => $emoji,
                    ],
                    [
                        'count' => 0,
                    ]
                );

                // Toggle reaction: if count is 0, increment to 1, else decrement to 0
                if ($reaction->count === 0) {
                    $reaction->incrementCount();
                } else {
                    $reaction->decrementCount();
                }
            });

            // Reload comment with reactions
            $comment->load('reactions');

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'reactions' => $comment->reactions->map(function ($reaction) {
                        return [
                            'emoji' => $reaction->emoji,
                            'count' => $reaction->count,
                        ];
                    }),
                ]);
            }

            return back()->with('success', 'Reaction updated.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
