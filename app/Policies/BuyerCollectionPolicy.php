<?php

namespace App\Policies;

use App\Models\BuyerCollection;
use App\Models\User;

class BuyerCollectionPolicy
{
    /**
     * Determine if the user can view the collection.
     */
    public function view(User $user, BuyerCollection $collection): bool
    {
        return $collection->user_id === $user->id;
    }

    /**
     * Determine if the user can update the collection.
     */
    public function update(User $user, BuyerCollection $collection): bool
    {
        return $collection->user_id === $user->id;
    }

    /**
     * Determine if the user can delete the collection.
     */
    public function delete(User $user, BuyerCollection $collection): bool
    {
        return $collection->user_id === $user->id;
    }
}
