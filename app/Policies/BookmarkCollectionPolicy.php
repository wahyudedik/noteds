<?php

namespace App\Policies;

use App\Models\BookmarkCollection;
use App\Models\User;

class BookmarkCollectionPolicy
{
    /**
     * Determine if the user can view the collection.
     */
    public function view(User $user, BookmarkCollection $collection): bool
    {
        return $collection->canUserView($user);
    }

    /**
     * Determine if the user can create collections.
     */
    public function create(User $user): bool
    {
        return !$user->is_banned;
    }

    /**
     * Determine if the user can update the collection.
     */
    public function update(User $user, BookmarkCollection $collection): bool
    {
        return $collection->canUserEdit($user);
    }

    /**
     * Determine if the user can delete the collection.
     */
    public function delete(User $user, BookmarkCollection $collection): bool
    {
        return $user->id === $collection->user_id && $collection->canBeDeleted();
    }

    /**
     * Determine if the user can share the collection.
     */
    public function share(User $user, BookmarkCollection $collection): bool
    {
        return $user->id === $collection->user_id;
    }
}
