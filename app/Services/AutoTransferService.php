<?php

namespace App\Services;

use App\Models\Clip;
use App\Models\Campaign;
use App\Services\WalletService;
use App\Services\RewardCalculationService;
use App\Services\RetryService;
use App\Services\ClipperLoggingService;
use Illuminate\Support\Facades\DB;
use Exception;

class AutoTransferService
{
    public function __construct(
        protected WalletService $walletService,
        protected RewardCalculationService $rewardCalculationService,
        protected RetryService $retryService,
        protected ClipperLoggingService $loggingService
    ) {}

    /**
     * Transfer reward to clipper wallet with retry mechanism.
     */
    public function transferRewardToClipper(Clip $clip): bool
    {
        try {
            return $this->retryService->retry(
                function () use ($clip) {
                    return $this->executeTransfer($clip);
                },
                [
                    'max_retries' => 3,
                    'initial_delay' => 60, // 1 minute
                    'max_delay' => 3600, // 1 hour
                    'multiplier' => 2,
                ],
                "transfer_reward_clip_{$clip->id}"
            );
        } catch (Exception $e) {
            // Log the failure
            $this->loggingService->logTransfer(
                'campaign',
                $clip->campaign->wallet->id ?? null,
                'clipper',
                $clip->clipper->clipperWallet->id ?? null,
                $clip->approved_reward,
                'reward',
                'clip',
                $clip->id,
                $e
            );

            // Mark clip for manual review
            $clip->update(['status' => 'transfer_failed']);

            return false;
        }
    }

    /**
     * Execute the actual transfer (without retry).
     */
    protected function executeTransfer(Clip $clip): bool
    {
        return DB::transaction(function () use ($clip) {
            if ($clip->status !== 'approved' || $clip->paid_at) {
                return false; // Already paid or not approved
            }

            $campaign = $clip->campaign;
            $campaignWallet = $this->walletService->getCampaignWallet($campaign);
            $clipperWallet = $this->walletService->getClipperWallet($clip->clipper);

            $reward = (float) $clip->approved_reward;
            if ($reward <= 0) {
                return false;
            }

            // Check if campaign has enough budget
            if ($campaignWallet->remaining_budget < $reward) {
                return false; // Insufficient budget
            }

            // Calculate platform fee
            $platformFee = $this->deductPlatformFee($reward);
            $netReward = $reward - $platformFee;

            // Deduct from campaign wallet
            if (!$campaignWallet->deductBudget($reward)) {
                return false;
            }

            // Add to clipper wallet (as pending, will be moved to available after validation)
            if (!$clipperWallet->addReward($netReward)) {
                // Rollback campaign wallet
                $campaignWallet->remaining_budget += $reward;
                $campaignWallet->save();
                return false;
            }

            // Add platform fee to platform wallet
            if ($platformFee > 0) {
                $platformWallet = $this->walletService->getPlatformWallet();
                $platformWallet->addFee($platformFee);
            }

            // Create ledger entries
            \App\Models\LedgerEntry::createEntry([
                'from_wallet_type' => 'campaign',
                'from_wallet_id' => $campaignWallet->id,
                'to_wallet_type' => 'clipper',
                'to_wallet_id' => $clipperWallet->id,
                'amount' => $netReward,
                'reason' => 'reward',
                'reference_type' => 'clip',
                'reference_id' => $clip->id,
            ]);

            if ($platformFee > 0) {
                \App\Models\LedgerEntry::createEntry([
                    'from_wallet_type' => 'campaign',
                    'from_wallet_id' => $campaignWallet->id,
                    'to_wallet_type' => 'platform',
                    'to_wallet_id' => $platformWallet->id,
                    'amount' => $platformFee,
                    'reason' => 'fee',
                    'reference_type' => 'clip',
                    'reference_id' => $clip->id,
                ]);
            }

            // Mark clip as paid
            $clip->markAsPaid();

            // Update campaign stats
            $campaign->increment('total_spent', $reward);
            $campaign->save();

            // Log successful transfer
            $this->loggingService->logTransfer(
                'campaign',
                $campaignWallet->id,
                'clipper',
                $clipperWallet->id,
                $netReward,
                'reward',
                'clip',
                $clip->id
            );

            return true;
        });
    }

    /**
     * Deduct platform fee from amount.
     */
    public function deductPlatformFee(float $amount): float
    {
        $feePercent = config('clipper.platform_fee_percent', 5);
        return round($amount * ($feePercent / 100), 2);
    }

    /**
     * Process approved clips in batch.
     */
    public function processApprovedClips(): int
    {
        $clips = Clip::where('status', 'approved')
            ->whereNull('paid_at')
            ->with(['campaign', 'clipper'])
            ->get();

        $processed = 0;

        foreach ($clips as $clip) {
            if ($this->transferRewardToClipper($clip)) {
                $processed++;
            }
        }

        return $processed;
    }
}

