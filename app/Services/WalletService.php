<?php

namespace App\Services;

use App\Models\User;
use App\Models\Campaign;
use App\Models\CreatorWallet;
use App\Models\ClipperWallet;
use App\Models\CampaignWallet;
use App\Models\PlatformWallet;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function __construct(
        private BalanceService $balanceService
    ) {}
    /**
     * Get or create creator wallet for user.
     */
    public function getCreatorWallet(User $user): CreatorWallet
    {
        return CreatorWallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'balance_available' => 0,
                'balance_locked' => 0,
            ]
        );
    }

    /**
     * Get or create clipper wallet for user.
     */
    public function getClipperWallet(User $user): ClipperWallet
    {
        return ClipperWallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'balance_pending' => 0,
                'balance_available' => 0,
                'balance_withdrawn' => 0,
            ]
        );
    }

    /**
     * Get or create campaign wallet for campaign.
     */
    public function getCampaignWallet(Campaign $campaign): CampaignWallet
    {
        return CampaignWallet::firstOrCreate(
            ['campaign_id' => $campaign->id],
            [
                'total_budget' => 0,
                'remaining_budget' => 0,
                'locked_amount' => 0,
            ]
        );
    }

    /**
     * Get or create platform wallet (singleton).
     */
    public function getPlatformWallet(): PlatformWallet
    {
        return PlatformWallet::getInstance();
    }

    /**
     * Transfer between wallets.
     */
    public function transferBetweenWallets(
        string $fromWalletType,
        ?string $fromWalletId,
        string $toWalletType,
        ?string $toWalletId,
        float $amount,
        string $reason,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?array $metadata = null
    ): bool {
        return DB::transaction(function () use (
            $fromWalletType,
            $fromWalletId,
            $toWalletType,
            $toWalletId,
            $amount,
            $reason,
            $referenceType,
            $referenceId,
            $metadata
        ) {
            // Deduct from source wallet
            $fromWallet = $this->getWalletByType($fromWalletType, $fromWalletId);
            if ($fromWallet && !$this->deductFromWallet($fromWallet, $fromWalletType, $amount)) {
                return false;
            }

            // Add to destination wallet
            $toWallet = $this->getWalletByType($toWalletType, $toWalletId);
            if ($toWallet && !$this->addToWallet($toWallet, $toWalletType, $amount)) {
                return false;
            }

            // Create ledger entry
            \App\Models\LedgerEntry::createEntry([
                'from_wallet_type' => $fromWalletType,
                'from_wallet_id' => $fromWalletId,
                'to_wallet_type' => $toWalletType,
                'to_wallet_id' => $toWalletId,
                'amount' => $amount,
                'reason' => $reason,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'metadata' => $metadata,
            ]);

            return true;
        });
    }

    /**
     * Get wallet by type and ID.
     */
    protected function getWalletByType(string $type, ?string $id)
    {
        if (!$id) {
            return null;
        }

        return match ($type) {
            'creator' => CreatorWallet::find($id),
            'clipper' => ClipperWallet::find($id),
            'campaign' => CampaignWallet::find($id),
            'platform' => PlatformWallet::find($id) ?? PlatformWallet::getInstance(),
            default => null,
        };
    }

    /**
     * Deduct from wallet.
     */
    protected function deductFromWallet($wallet, string $type, float $amount): bool
    {
        return match ($type) {
            'creator' => $wallet->deductBalance($amount),
            'clipper' => $wallet->lockForWithdrawal($amount),
            'campaign' => $wallet->deductBudget($amount),
            'platform' => true, // Platform wallet can go negative
            default => false,
        };
    }

    /**
     * Add to wallet.
     */
    protected function addToWallet($wallet, string $type, float $amount): bool
    {
        return match ($type) {
            'creator' => $wallet->addBalance($amount),
            'clipper' => $wallet->addReward($amount),
            'campaign' => true, // Campaign wallet is managed differently
            'platform' => $wallet->addFee($amount),
            default => false,
        };
    }

    /**
     * Sync user balance from wallet (for backward compatibility with BalanceService).
     * This maintains consistency between user.balance and wallet balances.
     */
    public function syncUserBalance(User $user, string $walletType = 'creator'): void
    {
        if ($walletType === 'creator') {
            $wallet = $this->getCreatorWallet($user);
            $walletBalance = $wallet->balance_available;
            
            // Update user balance if different
            if (abs($user->balance - $walletBalance) > 0.01) {
                $user->update(['balance' => $walletBalance]);
            }
        }
    }

    /**
     * Get user balance (wrapper for backward compatibility).
     * Returns creator wallet balance if user has one, otherwise uses BalanceService.
     */
    public function getUserBalance(User $user): float
    {
        // Try to get from creator wallet first
        if ($user->creatorWallet) {
            return (float) $user->creatorWallet->balance_available;
        }

        // Fallback to BalanceService for backward compatibility
        return $this->balanceService->getBalance($user);
    }

    /**
     * Add balance to user (wrapper that updates both wallet and user.balance).
     */
    public function addUserBalance(User $user, float $amount, string $description, ?string $referenceId = null): bool
    {
        return DB::transaction(function () use ($user, $amount, $description, $referenceId) {
            // Add to creator wallet
            $wallet = $this->getCreatorWallet($user);
            if (!$wallet->addBalance($amount)) {
                return false;
            }

            // Also update user.balance for backward compatibility
            $this->balanceService->addBalance($user, $amount, $description, $referenceId, 'wallet_topup');

            return true;
        });
    }

    /**
     * Deduct balance from user (wrapper that updates both wallet and user.balance).
     */
    public function deductUserBalance(User $user, float $amount, string $description, ?string $referenceId = null): bool
    {
        return DB::transaction(function () use ($user, $amount, $description, $referenceId) {
            // Check wallet balance first
            $wallet = $this->getCreatorWallet($user);
            if ($wallet->balance_available < $amount) {
                throw new \Exception('Insufficient balance');
            }

            // Deduct from wallet
            if (!$wallet->deductBalance($amount)) {
                return false;
            }

            // Also update user.balance for backward compatibility
            try {
                $this->balanceService->deductBalance($user, $amount, $description, $referenceId);
            } catch (\Exception $e) {
                // Rollback wallet deduction
                $wallet->addBalance($amount);
                throw $e;
            }

            return true;
        });
    }
}

