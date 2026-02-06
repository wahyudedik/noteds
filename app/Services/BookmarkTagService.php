<?php

namespace App\Services;

use App\Models\Bookmark;
use App\Models\BookmarkTag;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BookmarkTagService
{
    /**
     * Create or get existing tag.
     */
    public function createOrGetTag(string $name, ?User $user = null, bool $isGlobal = false): BookmarkTag
    {
        $slug = Str::slug($name);

        $tag = BookmarkTag::where('slug', $slug)
            ->where(function ($query) use ($user, $isGlobal) {
                if ($isGlobal) {
                    $query->where('is_global', true);
                } else {
                    $query->where('user_id', $user?->id);
                }
            })
            ->first();

        if (!$tag) {
            $tag = BookmarkTag::create([
                'name' => $name,
                'slug' => $slug,
                'user_id' => $isGlobal ? null : $user?->id,
                'is_global' => $isGlobal,
            ]);
        }

        return $tag;
    }

    /**
     * Sync tags for bookmark.
     */
    public function syncBookmarkTags(Bookmark $bookmark, array $tagNames, ?User $user = null): void
    {
        $tags = collect($tagNames)->map(function ($name) use ($user) {
            return $this->createOrGetTag($name, $user, false);
        });

        $tagIds = $tags->pluck('id')->toArray();

        // Update usage counts
        $oldTagIds = $bookmark->tags()->pluck('bookmark_tags.id')->toArray();
        $removedTagIds = array_diff($oldTagIds, $tagIds);
        $addedTagIds = array_diff($tagIds, $oldTagIds);

        foreach ($removedTagIds as $tagId) {
            $tag = BookmarkTag::find($tagId);
            if ($tag) {
                $tag->decrementUsage();
            }
        }

        foreach ($addedTagIds as $tagId) {
            $tag = BookmarkTag::find($tagId);
            if ($tag) {
                $tag->incrementUsage();
            }
        }

        if (!empty($removedTagIds)) {
            $bookmark->tags()->detach($removedTagIds);
        }

        foreach ($addedTagIds as $tagId) {
            $bookmark->tags()->attach($tagId, ['id' => (string) Str::uuid()]);
        }
    }

    /**
     * Get tag suggestions for autocomplete.
     */
    public function getTagSuggestions(string $query, ?User $user = null, int $limit = 10): Collection
    {
        return BookmarkTag::forUser($user)
            ->where('name', 'like', "%{$query}%")
            ->orderBy('usage_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Increment tag usage count.
     */
    public function incrementTagUsage(BookmarkTag $tag): void
    {
        $tag->incrementUsage();
    }

    /**
     * Decrement tag usage count.
     */
    public function decrementTagUsage(BookmarkTag $tag): void
    {
        $tag->decrementUsage();
    }
}
