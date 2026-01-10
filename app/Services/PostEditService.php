<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostEditHistory;
use Illuminate\Support\Facades\DB;

class PostEditService
{
    /**
     * Edit a post and save history.
     */
    public function editPost(Post $post, array $data, string $userId): Post
    {
        return DB::transaction(function () use ($post, $data, $userId) {
            // Save current state to history
            PostEditHistory::create([
                'post_id' => $post->id,
                'user_id' => $userId,
                'title' => $post->title,
                'content' => $post->content,
                'edited_at' => now(),
            ]);

            // Update post
            $post->update(array_merge($data, [
                'edited_at' => now(),
                'edit_count' => $post->edit_count + 1,
            ]));

            return $post->fresh();
        });
    }

    /**
     * Get edit history for a post.
     */
    public function getEditHistory(Post $post): \Illuminate\Database\Eloquent\Collection
    {
        return PostEditHistory::where('post_id', $post->id)
            ->with('user')
            ->orderBy('edited_at', 'desc')
            ->get();
    }
}

