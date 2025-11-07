<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NotePolicy
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
    public function view(User $user, Note $note): bool
    {
        return $user->id === $note->user_id || $note->is_public;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Note $note): bool
    {
        return $user->id === $note->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     * Note: Cannot delete if note has been sold (has transactions).
     */
    public function delete(User $user, Note $note): bool
    {
        // Only original creator (or current owner if not sold) can delete
        if ($user->id !== $note->user_id && $user->id !== $note->original_creator_id) {
            return false;
        }
        
        // Check if note has been sold (has any successful transactions)
        $hasTransactions = $note->transactions()
            ->where('status', 'success')
            ->exists();
        
        // Cannot delete if note has been sold
        if ($hasTransactions) {
            return false;
        }
        
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Note $note): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Note $note): bool
    {
        return false;
    }
}
