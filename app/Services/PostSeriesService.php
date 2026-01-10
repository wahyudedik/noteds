<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\DB;

class PostSeriesService
{
    /**
     * Create a new series with a root post.
     *
     * @param Post $post
     * @return Post
     */
    public function createSeries(Post $post): Post
    {
        $post->update([
            'series_id' => $post->id,
            'series_order' => 1,
            'is_series_root' => true,
        ]);

        return $post->fresh();
    }

    /**
     * Add a post to an existing series.
     *
     * @param Post $post
     * @param Post $seriesRoot
     * @return Post
     */
    public function addToSeries(Post $post, Post $seriesRoot): Post
    {
        if (!$seriesRoot->is_series_root) {
            throw new \InvalidArgumentException('Target post is not a series root');
        }

        // Get the next order number
        $maxOrder = Post::where('series_id', $seriesRoot->id)
            ->max('series_order') ?? 0;

        $post->update([
            'series_id' => $seriesRoot->id,
            'series_order' => $maxOrder + 1,
            'is_series_root' => false,
        ]);

        return $post->fresh();
    }

    /**
     * Remove a post from a series.
     *
     * @param Post $post
     * @return Post
     */
    public function removeFromSeries(Post $post): Post
    {
        if ($post->is_series_root) {
            // If removing root, convert series to individual posts
            $seriesPosts = Post::where('series_id', $post->id)
                ->where('id', '!=', $post->id)
                ->get();

            foreach ($seriesPosts as $seriesPost) {
                $seriesPost->update([
                    'series_id' => null,
                    'series_order' => null,
                ]);
            }

            $post->update([
                'series_id' => null,
                'series_order' => null,
                'is_series_root' => false,
            ]);
        } else {
            // Reorder remaining posts
            $oldOrder = $post->series_order;
            $seriesId = $post->series_id;

            $post->update([
                'series_id' => null,
                'series_order' => null,
            ]);

            // Update order of posts after the removed one
            Post::where('series_id', $seriesId)
                ->where('series_order', '>', $oldOrder)
                ->decrement('series_order');
        }

        return $post->fresh();
    }

    /**
     * Update the order of posts in a series.
     *
     * @param Post $post
     * @param int $newOrder
     * @return Post
     */
    public function updateOrder(Post $post, int $newOrder): Post
    {
        if (!$post->isInSeries()) {
            throw new \InvalidArgumentException('Post is not part of a series');
        }

        $seriesId = $post->series_id;
        $oldOrder = $post->series_order;

        if ($oldOrder === $newOrder) {
            return $post;
        }

        DB::transaction(function () use ($post, $seriesId, $oldOrder, $newOrder) {
            if ($oldOrder < $newOrder) {
                // Moving down
                Post::where('series_id', $seriesId)
                    ->where('series_order', '>', $oldOrder)
                    ->where('series_order', '<=', $newOrder)
                    ->decrement('series_order');
            } else {
                // Moving up
                Post::where('series_id', $seriesId)
                    ->where('series_order', '>=', $newOrder)
                    ->where('series_order', '<', $oldOrder)
                    ->increment('series_order');
            }

            $post->update(['series_order' => $newOrder]);
        });

        return $post->fresh();
    }

    /**
     * Get all posts in a series.
     *
     * @param Post $seriesRoot
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSeriesPosts(Post $seriesRoot)
    {
        if (!$seriesRoot->is_series_root) {
            return collect([]);
        }

        return Post::where(function ($query) use ($seriesRoot) {
                $query->where('id', $seriesRoot->id)
                    ->orWhere('series_id', $seriesRoot->id);
            })
            ->with(['user', 'media'])
            ->orderBy('series_order')
            ->get();
    }

    /**
     * Get navigation data for a post in a series.
     *
     * @param Post $post
     * @return array
     */
    public function getSeriesNavigation(Post $post): array
    {
        if (!$post->isInSeries()) {
            return [];
        }

        $seriesRoot = $post->is_series_root ? $post : $post->seriesRoot;
        $allPosts = $this->getSeriesPosts($seriesRoot);

        $currentIndex = $allPosts->search(function ($p) use ($post) {
            return $p->id === $post->id;
        });

        $previousPost = $currentIndex > 0 ? $allPosts[$currentIndex - 1] : null;
        $nextPost = $currentIndex < $allPosts->count() - 1 ? $allPosts[$currentIndex + 1] : null;

        return [
            'series_root' => $seriesRoot,
            'current_post' => $post,
            'current_index' => $currentIndex + 1,
            'total_posts' => $allPosts->count(),
            'previous_post' => $previousPost,
            'next_post' => $nextPost,
            'all_posts' => $allPosts,
        ];
    }

    /**
     * Get metadata for a series.
     *
     * @param Post $seriesRoot
     * @return array
     */
    public function getSeriesMetadata(Post $seriesRoot): array
    {
        if (!$seriesRoot->is_series_root) {
            return [];
        }

        $allPosts = $this->getSeriesPosts($seriesRoot);

        return [
            'series_root' => $seriesRoot,
            'total_posts' => $allPosts->count(),
            'total_views' => $allPosts->sum('total_views'),
            'total_upvotes' => $allPosts->sum('upvotes_count'),
            'total_comments' => $allPosts->sum('comments_count'),
            'created_at' => $seriesRoot->created_at,
            'updated_at' => $allPosts->max('updated_at'),
        ];
    }
}


