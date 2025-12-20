<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'min:10'],
            'parent_id' => ['nullable', 'exists:comments,id'],
        ]);

        DB::transaction(function () use ($request, $post, $validated) {
            $comment = Comment::create([
                'user_id' => $request->user()->id,
                'post_id' => $post->id,
                'parent_id' => $validated['parent_id'] ?? null,
                'content' => $validated['content'],
            ]);

            $post->increment('comments_count');
        });

        return back();
    }

    public function markBestAnswer(Request $request, Comment $comment): RedirectResponse
    {
        // Only post author can mark best answer
        if ($comment->post->user_id !== $request->user()->id) {
            return back()->withErrors(['error' => 'Only the post author can mark best answer.']);
        }

        DB::transaction(function () use ($comment) {
            // Unmark other best answers for this post
            Comment::where('post_id', $comment->post_id)
                ->where('id', '!=', $comment->id)
                ->update(['is_best_answer' => false]);

            // Mark this comment as best answer
            $comment->update(['is_best_answer' => true]);
        });

        return back();
    }
}
