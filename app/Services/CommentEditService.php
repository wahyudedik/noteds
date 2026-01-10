<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\CommentEditHistory;
use Illuminate\Support\Facades\DB;

class CommentEditService
{
    /**
     * Edit a comment and save history.
     */
    public function editComment(Comment $comment, array $data, string $userId): Comment
    {
        return DB::transaction(function () use ($comment, $data, $userId) {
            // Save current state to history
            CommentEditHistory::create([
                'comment_id' => $comment->id,
                'user_id' => $userId,
                'content' => $comment->content,
                'edited_at' => now(),
            ]);

            // Update comment
            $comment->update(array_merge($data, [
                'edited_at' => now(),
                'edit_count' => $comment->edit_count + 1,
            ]));

            return $comment->fresh();
        });
    }

    /**
     * Get edit history for a comment.
     */
    public function getEditHistory(Comment $comment): \Illuminate\Database\Eloquent\Collection
    {
        return CommentEditHistory::where('comment_id', $comment->id)
            ->with('user')
            ->orderBy('edited_at', 'desc')
            ->get();
    }
}

