<?php

namespace App\Services;

use App\Models\BookmarkCollection;
use App\Models\User;
use Illuminate\Support\Collection;

class BookmarkCollectionService
{
    /**
     * Create default collection for user.
     */
    public function createDefaultCollection(User $user): BookmarkCollection
    {
        return BookmarkCollection::firstOrCreate(
            [
                'user_id' => $user->id,
                'is_default' => true,
            ],
            [
                'name' => 'All Bookmarks',
                'description' => 'Default collection for all bookmarks',
                'is_default' => true,
            ]
        );
    }

    /**
     * Move collection to different parent.
     */
    public function moveCollection(BookmarkCollection $collection, ?BookmarkCollection $newParent): void
    {
        // Prevent moving to itself or its own children
        if ($newParent && ($newParent->id === $collection->id || $this->isDescendant($newParent, $collection))) {
            throw new \Exception('Cannot move collection to itself or its own children.');
        }

        $collection->parent_id = $newParent?->id;
        $collection->save();
    }

    /**
     * Check if collection is descendant of another.
     */
    private function isDescendant(BookmarkCollection $ancestor, BookmarkCollection $descendant): bool
    {
        $current = $descendant->parent;
        while ($current) {
            if ($current->id === $ancestor->id) {
                return true;
            }
            $current = $current->parent;
        }
        return false;
    }

    /**
     * Delete collection with handling bookmarks.
     */
    public function deleteCollection(BookmarkCollection $collection): void
    {
        if (!$collection->canBeDeleted()) {
            throw new \Exception('Cannot delete collection with bookmarks or child collections.');
        }

        $collection->delete();
    }

    /**
     * Get collection tree structure.
     */
    public function getCollectionTree(User $user): Collection
    {
        $collections = BookmarkCollection::forUser($user)
            ->with('children')
            ->root()
            ->ordered()
            ->get();

        return $this->buildTree($collections);
    }

    /**
     * Build nested tree structure.
     */
    private function buildTree(Collection $collections, ?string $parentId = null): Collection
    {
        return $collections->filter(function ($collection) use ($parentId) {
            return $collection->parent_id === $parentId;
        })->map(function ($collection) use ($collections) {
            $collection->children = $this->buildTree($collections, $collection->id);
            return $collection;
        });
    }
}

