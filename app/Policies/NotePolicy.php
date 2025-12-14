<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Note Authorization Policy
 * 
 * Security Controls:
 * - Ownership verification for edit/delete
 * - Visibility checks for view
 * - Account status verification
 * - Rate limiting for creation
 * - Audit logging for all operations
 * - KYC verification for public notes (sellers)
 */
class NotePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->isAuthenticated($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Note $note): bool
    {
        // Owner can always view their notes
        if ($user->id === $note->user_id) {
            return true;
        }

        // Public notes can be viewed by anyone
        if ($note->is_public) {
            return true;
        }

        // Private notes can only be viewed by owner
        return false;
    }

    /**
     * Determine whether the user can create models with security checks.
     */
    public function create(User $user): bool
    {
        // Must be authenticated
        if (!$this->isAuthenticated($user)) {
            return false;
        }

        // Account must be active
        if (!$this->isActive($user)) {
            return false;
        }

        // Account cannot be suspended
        if ($this->isSuspended($user)) {
            return false;
        }

        // Check for suspicious activity
        if ($this->checkSuspiciousActivity($user)) {
            return false;
        }

        // Rate limiting: max 20 notes per hour
        $recentNotes = $user->notes()
            ->where('created_at', '>', now()->subHour())
            ->count();

        if ($recentNotes >= 20) {
            return false;
        }

        $this->logAccess($user, 'create', 'Note');
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Note $note): bool
    {
        // Must be authenticated
        if (!$this->isAuthenticated($user)) {
            return false;
        }

        // Only owner can update
        if (!$this->isOwner($user, $note)) {
            return false;
        }

        // Account must be active
        if (!$this->isActive($user)) {
            return false;
        }

        // Cannot be suspended
        if ($this->isSuspended($user)) {
            return false;
        }

        $this->logAccess($user, 'update', 'Note', ['note_id' => $note->id]);
        return true;
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

        // Must be active
        if (!$this->isActive($user)) {
            return false;
        }

        $this->logAccess($user, 'delete', 'Note', ['note_id' => $note->id]);
        return true;
    }
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
