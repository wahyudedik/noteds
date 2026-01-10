<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Repost;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class QuoteRepostService
{
    /**
     * Create quote repost dengan post baru.
     */
    public function createQuoteRepost(User $user, Post $originalPost, string $quoteContent, string $displayMode = 'embedded'): Repost
    {
        return DB::transaction(function () use ($user, $originalPost, $quoteContent, $displayMode) {
            $quotePost = null;

            // If separate mode, create a new post
            if ($displayMode === 'separate') {
                $quotePost = Post::create([
                    'user_id' => $user->id,
                    'original_post_id' => $originalPost->id,
                    'is_quote_repost' => true,
                    'purpose_type' => $originalPost->purpose_type,
                    'title' => 'Quote: ' . $originalPost->title,
                    'content' => $quoteContent,
                    'status' => 'active',
                ]);
            }

            // Create repost record
            $repost = Repost::create([
                'user_id' => $user->id,
                'post_id' => $originalPost->id,
                'is_quote_repost' => true,
                'quote_content' => $quoteContent,
                'quote_post_id' => $quotePost?->id,
                'display_mode' => $displayMode,
            ]);

            return $repost;
        });
    }

    /**
     * Update quote content.
     */
    public function updateQuoteContent(Repost $repost, string $quoteContent): void
    {
        DB::transaction(function () use ($repost, $quoteContent) {
            $repost->update(['quote_content' => $quoteContent]);

            // If separate mode, also update the post
            if ($repost->display_mode === 'separate' && $repost->quote_post_id) {
                $quotePost = Post::find($repost->quote_post_id);
                if ($quotePost) {
                    $quotePost->update(['content' => $quoteContent]);
                }
            }
        });
    }

    /**
     * Delete quote repost dan post terkait.
     */
    public function deleteQuoteRepost(Repost $repost): void
    {
        DB::transaction(function () use ($repost) {
            // Delete the quote post if it exists
            if ($repost->quote_post_id) {
                Post::where('id', $repost->quote_post_id)->delete();
            }

            // Delete the repost (this will auto-decrement reposts_count)
            $repost->delete();
        });
    }

    /**
     * Convert separate post to embedded.
     */
    public function convertToEmbedded(Repost $repost): void
    {
        DB::transaction(function () use ($repost) {
            if ($repost->display_mode !== 'separate' || !$repost->quote_post_id) {
                return;
            }

            // Get quote content from post
            $quotePost = Post::find($repost->quote_post_id);
            if ($quotePost) {
                $repost->update([
                    'quote_content' => $quotePost->content,
                    'display_mode' => 'embedded',
                ]);

                // Delete the separate post
                $quotePost->delete();
                $repost->update(['quote_post_id' => null]);
            }
        });
    }

    /**
     * Convert embedded to separate post.
     */
    public function convertToSeparate(Repost $repost, Post $originalPost): void
    {
        DB::transaction(function () use ($repost, $originalPost) {
            if ($repost->display_mode !== 'embedded') {
                return;
            }

            // Create new post
            $quotePost = Post::create([
                'user_id' => $repost->user_id,
                'original_post_id' => $originalPost->id,
                'is_quote_repost' => true,
                'purpose_type' => $originalPost->purpose_type,
                'title' => 'Quote: ' . $originalPost->title,
                'content' => $repost->quote_content,
                'status' => 'active',
            ]);

            $repost->update([
                'quote_post_id' => $quotePost->id,
                'display_mode' => 'separate',
            ]);
        });
    }
}

