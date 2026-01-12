<?php

namespace App\Policies;

use App\Models\SupplierReview;
use App\Models\User;

class SupplierReviewPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SupplierReview $supplierReview): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create review
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SupplierReview $supplierReview): bool
    {
        return $user->id === $supplierReview->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SupplierReview $supplierReview): bool
    {
        return $user->id === $supplierReview->user_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SupplierReview $supplierReview): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SupplierReview $supplierReview): bool
    {
        return $user->isAdmin();
    }
}
