<?php

namespace App\Policies;

use App\Models\AffiliateLink;
use App\Models\User;

class AffiliateLinkPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view their own affiliate links
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AffiliateLink $affiliateLink): bool
    {
        return $affiliateLink->affiliate_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create affiliate links
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AffiliateLink $affiliateLink): bool
    {
        return $affiliateLink->affiliate_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AffiliateLink $affiliateLink): bool
    {
        return $affiliateLink->affiliate_id === $user->id;
    }
}
