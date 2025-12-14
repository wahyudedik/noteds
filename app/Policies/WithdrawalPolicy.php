<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Withdrawal;

/**
 * Withdrawal Authorization Policy
 * 
 * Security Controls for Sensitive Financial Operations:
 * - Strict seller verification
 * - Minimum wallet balance checks
 * - KYC verification mandatory
 * - Bank account verification required
 * - Rate limiting (5 per day) to prevent abuse
 * - Fraud detection checks
 * - All operations logged with full audit trail
 * - Admin approval required for amounts over threshold
 */
class WithdrawalPolicy extends BasePolicy
{
    /**
     * Maximum withdrawal amount without admin review
     */
    protected const AUTO_APPROVE_LIMIT = 5000; // In base currency

    /**
     * User can view withdrawal if involved or admin
     */
    public function view(User $user, Withdrawal $withdrawal): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $user->id === $withdrawal->user_id;
    }

    /**
     * Only sellers can request withdrawals
     */
    public function create(User $user): bool
    {
        // Authentication and status checks
        if (!$this->isAuthenticated($user)) {
            return false;
        }

        if (!$this->isActive($user)) {
            return false;
        }

        if ($this->isSuspended($user)) {
            return false;
        }

        // Must be seller
        if (!$this->isSeller($user)) {
            return false;
        }

        // KYC verification is MANDATORY for withdrawals
        if (!$this->hasKyc($user)) {
            return false;
        }

        // Must have verified bank account
        $bankAccount = $user->bankAccount()
            ->where('verified', true)
            ->exists();

        if (!$bankAccount) {
            return false;
        }

        // Check minimum wallet balance requirement
        if (!$this->hasSufficientBalance($user, 100)) { // Minimum $100
            return false;
        }

        // Rate limiting: max 5 withdrawal requests per day
        $recentWithdrawals = $user->withdrawals()
            ->where('created_at', '>', now()->subDay())
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        if ($recentWithdrawals >= 5) {
            return false;
        }

        // Check for suspicious activity
        if ($this->checkSuspiciousActivity($user)) {
            return false;
        }

        // Check if user has pending disputes
        if ($user->buyerDisputes()
            ->where('status', 'open')
            ->exists()
        ) {
            return false; // Cannot withdraw while disputes pending
        }

        $this->logAccess($user, 'create', 'Withdrawal');
        return true;
    }

    /**
     * Only admin can approve withdrawals
     */
    public function approve(User $user, Withdrawal $withdrawal): bool
    {
        if (!$this->isAdmin($user)) {
            return false;
        }

        // Can only approve pending withdrawals
        if ($withdrawal->status !== 'pending') {
            return false;
        }

        // Verify user still has sufficient balance
        $withdrawalUser = $withdrawal->user;
        if (!$this->hasSufficientBalance($withdrawalUser, $withdrawal->amount)) {
            return false; // Balance no longer sufficient
        }

        $this->logAccess($user, 'approve', 'Withdrawal', [
            'withdrawal_id' => $withdrawal->id,
            'amount' => $withdrawal->amount,
            'user_id' => $withdrawal->user_id,
        ]);
        return true;
    }

    /**
     * Only admin can reject withdrawals with explanation
     */
    public function reject(User $user, Withdrawal $withdrawal): bool
    {
        if (!$this->isAdmin($user)) {
            return false;
        }

        // Can only reject pending withdrawals
        if ($withdrawal->status !== 'pending') {
            return false;
        }

        $this->logAccess($user, 'reject', 'Withdrawal', [
            'withdrawal_id' => $withdrawal->id,
            'amount' => $withdrawal->amount,
            'user_id' => $withdrawal->user_id,
        ]);
        return true;
    }

    /**
     * User cannot update withdrawal after creation
     */
    public function update(User $user, Withdrawal $withdrawal): bool
    {
        return false; // Withdrawals are immutable
    }

    /**
     * Only admin can cancel with special permission
     */
    public function cancel(User $user, Withdrawal $withdrawal): bool
    {
        if (!$this->isAdmin($user)) {
            return false;
        }

        // Can only cancel pending/approved withdrawals
        if (!in_array($withdrawal->status, ['pending', 'approved'])) {
            return false;
        }

        $this->logAccess($user, 'cancel', 'Withdrawal', [
            'withdrawal_id' => $withdrawal->id,
            'reason' => 'Admin cancellation',
        ]);
        return true;
    }

    /**
     * User can dispute completed withdrawal (initiated fraudulently)
     */
    public function dispute(User $user, Withdrawal $withdrawal): bool
    {
        if (!$this->isAuthenticated($user)) {
            return false;
        }

        // Only withdrawal owner can dispute
        if ($user->id !== $withdrawal->user_id) {
            return false;
        }

        // Can only dispute completed withdrawals
        if ($withdrawal->status !== 'completed') {
            return false;
        }

        // Must be within 30 days of completion
        if ($withdrawal->completed_at->diffInDays(now()) > 30) {
            return false;
        }

        $this->logAccess($user, 'dispute', 'Withdrawal', [
            'withdrawal_id' => $withdrawal->id,
        ]);
        return true;
    }
}
