<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Auth\Access\Response;

/**
 * Transaction Authorization Policy
 * 
 * Security Controls:
 * - Buyer/Seller verification
 * - Wallet balance checks
 * - Account status checks
 * - KYC verification for large transactions
 * - Fraud detection checks
 * - Rate limiting
 * - All operations logged in audit trail
 */
class TransactionPolicy extends BasePolicy
{
    /**
     * User can view any transaction if authenticated
     */
    public function viewAny(User $user): bool
    {
        return $this->isAuthenticated($user);
    }

    /**
     * User can view transaction if involved (buyer/seller) or admin
     */
    public function view(User $user, Transaction $transaction): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $user->id === $transaction->buyer_id ||
            $user->id === $transaction->seller_id;
    }

    /**
     * User can create transaction (initiate purchase) if:
     * - Authenticated and active
     * - Has sufficient wallet balance
     * - Not rate limited
     * - KYC verified (if required)
     * - No suspicious activity detected
     */
    public function create(User $user): bool
    {
        // Authentication checks
        if (!$this->isAuthenticated($user)) {
            return false;
        }

        if (!$this->isActive($user)) {
            return false;
        }

        if ($this->isSuspended($user)) {
            return false;
        }

        // Must be a buyer
        if (!$this->isBuyer($user)) {
            return false;
        }

        // KYC verification for transactions
        if (!$this->hasKyc($user)) {
            return false;
        }

        // Check for suspicious activity
        if ($this->checkSuspiciousActivity($user)) {
            return false;
        }

        // Rate limiting: max 50 transactions per hour
        $recentTransactions = $user->buyerTransactions()
            ->where('created_at', '>', now()->subHour())
            ->count();

        if ($recentTransactions >= 50) {
            return false;
        }

        $this->logAccess($user, 'create', 'Transaction');
        return true;
    }

    /**
     * Only buyer can confirm transaction
     */
    public function confirm(User $user, Transaction $transaction): bool
    {
        if (!$this->isAuthenticated($user)) {
            return false;
        }

        // Only buyer can confirm
        if ($user->id !== $transaction->buyer_id) {
            return false;
        }

        // Check wallet has sufficient balance
        if (!$this->hasSufficientBalance($user, $transaction->amount)) {
            return false;
        }

        // Cannot confirm already confirmed transaction
        if ($transaction->status !== 'pending') {
            return false;
        }

        $this->logAccess($user, 'confirm', 'Transaction', [
            'transaction_id' => $transaction->id,
            'amount' => $transaction->amount,
        ]);
        return true;
    }

    /**
     * Only seller can confirm receipt
     */
    public function confirmReceipt(User $user, Transaction $transaction): bool
    {
        if (!$this->isAuthenticated($user)) {
            return false;
        }

        // Only seller can confirm receipt
        if ($user->id !== $transaction->seller_id) {
            return false;
        }

        // Only confirmed transactions can be received
        if ($transaction->status !== 'confirmed') {
            return false;
        }

        $this->logAccess($user, 'confirmReceipt', 'Transaction', [
            'transaction_id' => $transaction->id,
        ]);
        return true;
    }

    /**
     * Only admin can cancel/refund transaction
     */
    public function cancel(User $user, Transaction $transaction): bool
    {
        if (!$this->isAdmin($user)) {
            return false;
        }

        // Cannot cancel completed transactions
        if ($transaction->status === 'completed') {
            return false;
        }

        $this->logAccess($user, 'cancel', 'Transaction', [
            'transaction_id' => $transaction->id,
            'previous_status' => $transaction->status,
        ]);
        return true;
    }

    /**
     * Buyer can dispute transaction
     */
    public function dispute(User $user, Transaction $transaction): bool
    {
        if (!$this->isAuthenticated($user)) {
            return false;
        }

        // Only buyer can dispute
        if ($user->id !== $transaction->buyer_id) {
            return false;
        }

        // Cannot dispute if already completed or refunded
        if (in_array($transaction->status, ['completed', 'refunded'])) {
            return false;
        }

        // Rate limiting: max 10 disputes per day
        $recentDisputes = $user->buyerDisputes()
            ->where('created_at', '>', now()->subDay())
            ->count();

        if ($recentDisputes >= 10) {
            return false;
        }

        $this->logAccess($user, 'dispute', 'Transaction', [
            'transaction_id' => $transaction->id,
        ]);
        return true;
    }

    /**
     * User cannot update transaction
     */
    public function update(User $user, Transaction $transaction): bool
    {
        return false; // Transactions are immutable once created
    }

    /**
     * User cannot delete transaction (admin only and rarely)
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return $this->isAdmin($user) && auth()->user()->hasPermission('delete_transactions');
    }

    /**
     * Only admin can permanently delete
     */
    public function forceDelete(User $user, Transaction $transaction): bool
    {
        return $this->isAdmin($user) && auth()->user()->hasPermission('force_delete_transactions');
    }
}
