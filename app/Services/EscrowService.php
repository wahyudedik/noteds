<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CreatorWallet;
use App\Models\CampaignWallet;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;

class EscrowService
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    /**
     * Lock campaign budget from creator wallet to campaign wallet.
     */
    public function lockCampaignBudget(Campaign $campaign): bool
    {
        return DB::transaction(function () use ($campaign) {
            $creatorWallet = $this->walletService->getCreatorWallet($campaign->creator);
            $campaignWallet = $this->walletService->getCampaignWallet($campaign);

            // Check if creator has enough balance
            if ($creatorWallet->balance_available < $campaign->max_budget) {
                return false;
            }

            // Lock amount from creator wallet
            if (!$creatorWallet->lockAmount($campaign->max_budget)) {
                return false;
            }

            // Lock budget in campaign wallet
            if (!$campaignWallet->lockBudget($campaign->max_budget)) {
                // Rollback creator wallet lock
                $creatorWallet->unlockAmount($campaign->max_budget);
                return false;
            }

            // Create ledger entry
            \App\Models\LedgerEntry::createEntry([
                'from_wallet_type' => 'creator',
                'from_wallet_id' => $creatorWallet->id,
                'to_wallet_type' => 'campaign',
                'to_wallet_id' => $campaignWallet->id,
                'amount' => $campaign->max_budget,
                'reason' => 'campaign_lock',
                'reference_type' => 'campaign',
                'reference_id' => $campaign->id,
            ]);

            return true;
        });
    }

    /**
     * Release campaign budget back to creator wallet.
     */
    public function releaseCampaignBudget(Campaign $campaign): bool
    {
        return DB::transaction(function () use ($campaign) {
            $creatorWallet = $this->walletService->getCreatorWallet($campaign->creator);
            $campaignWallet = $this->walletService->getCampaignWallet($campaign);

            $lockedAmount = $campaignWallet->locked_amount;

            if ($lockedAmount <= 0) {
                return false;
            }

            // Release from campaign wallet
            if (!$campaignWallet->releaseBudget()) {
                return false;
            }

            // Unlock in creator wallet
            if (!$creatorWallet->unlockAmount($lockedAmount)) {
                // Rollback campaign wallet
                $campaignWallet->lockBudget($lockedAmount);
                return false;
            }

            // Create ledger entry
            \App\Models\LedgerEntry::createEntry([
                'from_wallet_type' => 'campaign',
                'from_wallet_id' => $campaignWallet->id,
                'to_wallet_type' => 'creator',
                'to_wallet_id' => $creatorWallet->id,
                'amount' => $lockedAmount,
                'reason' => 'campaign_unlock',
                'reference_type' => 'campaign',
                'reference_id' => $campaign->id,
            ]);

            return true;
        });
    }

    /**
     * Refund remaining budget to creator wallet.
     */
    public function refundRemainingBudget(Campaign $campaign): bool
    {
        return DB::transaction(function () use ($campaign) {
            $creatorWallet = $this->walletService->getCreatorWallet($campaign->creator);
            $campaignWallet = $this->walletService->getCampaignWallet($campaign);

            $remainingBudget = $campaignWallet->refund();

            if ($remainingBudget <= 0) {
                return true; // Nothing to refund
            }

            // Unlock in creator wallet
            if (!$creatorWallet->unlockAmount($remainingBudget)) {
                return false;
            }

            // Create ledger entry
            \App\Models\LedgerEntry::createEntry([
                'from_wallet_type' => 'campaign',
                'from_wallet_id' => $campaignWallet->id,
                'to_wallet_type' => 'creator',
                'to_wallet_id' => $creatorWallet->id,
                'amount' => $remainingBudget,
                'reason' => 'refund',
                'reference_type' => 'campaign',
                'reference_id' => $campaign->id,
            ]);

            return true;
        });
    }
}

