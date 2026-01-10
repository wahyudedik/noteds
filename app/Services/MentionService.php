<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Comment;
use App\Models\PostMention;
use App\Models\CommentMention;
use App\Models\User;
use App\Notifications\MentionedInPostNotification;
use App\Notifications\MentionedInCommentNotification;

class MentionService
{
    /**
     * Extract mentions from content.
     */
    public function extractMentions(string $content): array
    {
        preg_match_all('/@(\w+)/', $content, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Process mentions for a post.
     */
    public function processPostMentions(Post $post, array $mentionUsernames): void
    {
        // Clear existing mentions
        $post->mentions()->delete();

        foreach ($mentionUsernames as $username) {
            $user = User::where('name', $username)
                ->orWhere('email', $username)
                ->first();

            if ($user && $user->id !== $post->user_id) {
                PostMention::create([
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                ]);

                // Send notification
                $user->notify(new MentionedInPostNotification($post));
            }
        }
    }

    /**
     * Process mentions for a comment.
     */
    public function processCommentMentions(Comment $comment, array $mentionUsernames): void
    {
        // Clear existing mentions
        $comment->mentions()->delete();

        foreach ($mentionUsernames as $username) {
            $user = User::where('name', $username)
                ->orWhere('email', $username)
                ->first();

            if ($user && $user->id !== $comment->user_id) {
                CommentMention::create([
                    'comment_id' => $comment->id,
                    'user_id' => $user->id,
                ]);

                // Send notification
                $user->notify(new MentionedInCommentNotification($comment));
            }
        }
    }
}

