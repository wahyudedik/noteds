<?php

namespace App\Policies;

use App\Models\Bookmark;
use App\Models\Post;
use App\Models\User;

class BookmarkPolicy
{
    /**
     * Determine if the user can bookmark a post.
     */
    public function create(User $user, Post $post): bool
    {
        // Cannot bookmark if banned
        if ($user->is_banned) {
            return false;
        }

        // Post must be active
        if ($post->status !== 'active') {
            return false;
        }

        return true;
    }

    /**
     * Determine if the user can remove a bookmark.
     */
    public function delete(User $user, Bookmark $bookmark): bool
    {
        // Can only delete own bookmarks
        return $user->id === $bookmark->user_id;
    }

    /**
     * Determine if the user can view bookmarks.
     */
    public function viewAny(User $user): bool
    {
        // Can always view own bookmarks
        return true;
    }

    /**
     * Determine if the user can view a bookmark.
     */
    public function view(User $user, Bookmark $bookmark): bool
    {
        return $user->id === $bookmark->user_id;
    }

    /**
     * Determine if the user can update bookmark notes.
     */
    public function updateNotes(User $user, Bookmark $bookmark): bool
    {
        return $user->id === $bookmark->user_id;
    }

    /**
     * Determine if the user can manage bookmark tags.
     */
    public function manageTags(User $user, Bookmark $bookmark): bool
    {
        return $user->id === $bookmark->user_id;
    }
}

