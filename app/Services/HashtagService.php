<?php

namespace App\Services;

use App\Models\Hashtag;
use App\Models\Post;
use Illuminate\Support\Str;

class HashtagService
{
    /**
     * Extract hashtags from content.
     */
    public function extractHashtags(string $content): array
    {
        preg_match_all('/#(\w+)/', $content, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Normalize hashtag name (lowercase, trim).
     */
    public function normalizeHashtag(string $name): string
    {
        return strtolower(trim($name));
    }

    /**
     * Sync hashtags for a post.
     */
    public function syncHashtags(Post $post, array $hashtagNames): void
    {
        // Normalize hashtag names
        $normalizedNames = array_map([$this, 'normalizeHashtag'], $hashtagNames);
        $normalizedNames = array_unique($normalizedNames);

        $hashtagIds = [];

        foreach ($normalizedNames as $name) {
            if (empty($name)) {
                continue;
            }

            $hashtag = Hashtag::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );

            $hashtagIds[] = $hashtag->id;
        }

        // Sync hashtags
        $post->hashtags()->sync($hashtagIds);

        // Update posts_count for affected hashtags
        $this->updatePostsCount($hashtagIds);
    }

    /**
     * Update posts_count for hashtags.
     */
    protected function updatePostsCount(array $hashtagIds): void
    {
        foreach ($hashtagIds as $hashtagId) {
            $hashtag = Hashtag::find($hashtagId);
            if ($hashtag) {
                $hashtag->posts_count = $hashtag->posts()->count();
                $hashtag->save();
            }
        }
    }

    /**
     * Get trending hashtags.
     */
    public function getTrending(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Hashtag::orderBy('posts_count', 'desc')
            ->limit($limit)
            ->get();
    }
}

