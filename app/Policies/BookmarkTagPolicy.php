<?php

namespace App\Policies;

use App\Models\BookmarkTag;
use App\Models\User;

class BookmarkTagPolicy
{
    /**
     * Determine if the user can view tags.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the tag.
     */
    public function view(User $user, BookmarkTag $tag): bool
    {
        // Can view global tags or own tags
        return $tag->is_global || $tag->user_id === $user->id;
    }

    /**
     * Determine if the user can create tags.
     */
    public function create(User $user): bool
    {
        return !$user->is_banned;
    }

    /**
     * Determine if the user can update the tag.
     */
    public function update(User $user, BookmarkTag $tag): bool
    {
        return $tag->user_id === $user->id;
    }

    /**
     * Determine if the user can delete the tag.
     */
    public function delete(User $user, BookmarkTag $tag): bool
    {
        return $tag->user_id === $user->id;
    }

    /**
     * Determine if the user can toggle global status.
     */
    public function toggleGlobal(User $user, BookmarkTag $tag): bool
    {
        return $tag->user_id === $user->id;
    }
}
