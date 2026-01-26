<?php

namespace App\Services;

use App\Models\Clip;
use App\Models\Campaign;
use App\Services\WalletService;
use App\Services\RewardCalculationService;
use App\Services\RetryService;
use App\Services\ClipperLoggingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        try {
            return DB::transaction(function () use ($clip) {
                // Validate clip exists
                if (!$clip || !$clip->exists) {
                    throw new Exception('Clip not found. The clip may have been deleted.');
                }

                if ($clip->status !== 'approved') {
                    throw new Exception("Cannot transfer reward. Clip status is '{$clip->status}'. Only approved clips can receive rewards.");
                }

                if ($clip->paid_at) {
                    throw new Exception('This clip has already been paid. Duplicate transfer attempt prevented.');
                }

                // Validate campaign exists
                $campaign = $clip->campaign;
                if (!$campaign || !$campaign->exists) {
                    throw new Exception('Cannot transfer reward. The associated campaign has been deleted.');
                }

                try {
                    $campaignWallet = $this->walletService->getCampaignWallet($campaign);
                    $clipperWallet = $this->walletService->getClipperWallet($clip->clipper);
                } catch (Exception $e) {
                    Log::error('Failed to get wallets for transfer', [
                        'clip_id' => $clip->id,
                        'campaign_id' => $campaign->id ?? null,
                        'clipper_id' => $clip->clipper_id ?? null,
                        'error' => $e->getMessage(),
                    ]);
                    throw new Exception('Failed to access wallet. Please contact support.');
                }

                $reward = (float) $clip->approved_reward;
                if ($reward <= 0) {
                    throw new Exception('Invalid reward amount. Reward must be greater than 0.');
                }

                // Check if campaign has enough budget
                if ($campaignWallet->remaining_budget < $reward) {
                    throw new Exception("Insufficient campaign budget. Required: Rp " . number_format($reward, 0, ',', '.') . ", Available: Rp " . number_format($campaignWallet->remaining_budget, 0, ',', '.'));
                }

            // Calculate platform fee
            $platformFee = $this->deductPlatformFee($reward);
            $netReward = $reward - $platformFee;

                // Deduct from campaign wallet
                try {
                    if (!$campaignWallet->deductBudget($reward)) {
                        throw new Exception('Failed to deduct budget from campaign wallet.');
                    }
                } catch (Exception $e) {
                    Log::error('Failed to deduct campaign budget', [
                        'clip_id' => $clip->id,
                        'campaign_id' => $campaign->id,
                        'reward' => $reward,
                        'error' => $e->getMessage(),
                    ]);
                    throw new Exception('Failed to process payment. Please contact support.');
                }

                // Add to clipper wallet (as pending, will be moved to available after validation)
                try {
                    if (!$clipperWallet->addReward($netReward)) {
                        // Rollback campaign wallet
                        try {
                            $campaignWallet->remaining_budget += $reward;
                            $campaignWallet->save();
                        } catch (Exception $rollbackError) {
                            Log::error('Failed to rollback campaign budget after clipper wallet failure', [
                                'clip_id' => $clip->id,
                                'error' => $rollbackError->getMessage(),
                            ]);
                        }
                        throw new Exception('Failed to add reward to clipper wallet.');
                    }
                } catch (Exception $e) {
                    // Rollback campaign wallet
                    try {
                        $campaignWallet->remaining_budget += $reward;
                        $campaignWallet->save();
                    } catch (Exception $rollbackError) {
                        Log::error('Failed to rollback campaign budget', [
                            'clip_id' => $clip->id,
                            'error' => $rollbackError->getMessage(),
                        ]);
                    }
                    Log::error('Failed to add reward to clipper wallet', [
                        'clip_id' => $clip->id,
                        'clipper_id' => $clip->clipper_id,
                        'net_reward' => $netReward,
                        'error' => $e->getMessage(),
                    ]);
                    throw new Exception('Failed to process reward transfer. Please contact support.');
                }

                // Add platform fee to platform wallet
                if ($platformFee > 0) {
                    try {
                        $platformWallet = $this->walletService->getPlatformWallet();
                        $platformWallet->addFee($platformFee);
                    } catch (Exception $e) {
                        Log::warning('Failed to add platform fee', [
                            'clip_id' => $clip->id,
                            'platform_fee' => $platformFee,
                            'error' => $e->getMessage(),
                        ]);
                        // Don't fail the transaction for platform fee issues
                    }
                }

                // Create ledger entries
                try {
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
                            'to_wallet_id' => $platformWallet->id ?? null,
                            'amount' => $platformFee,
                            'reason' => 'fee',
                            'reference_type' => 'clip',
                            'reference_id' => $clip->id,
                        ]);
                    }
                } catch (Exception $e) {
                    Log::warning('Failed to create ledger entries', [
                        'clip_id' => $clip->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Don't fail the transaction for ledger entry issues
                }

                // Mark clip as paid
                try {
                    if (!$clip->markAsPaid()) {
                        throw new Exception('Failed to mark clip as paid.');
                    }
                } catch (Exception $e) {
                    Log::error('Failed to mark clip as paid', [
                        'clip_id' => $clip->id,
                        'error' => $e->getMessage(),
                    ]);
                    throw new Exception('Failed to complete transfer. Please contact support.');
                }

                // Update campaign stats
                try {
                    $campaign->increment('total_spent', $reward);
                    $campaign->save();
                } catch (Exception $e) {
                    Log::warning('Failed to update campaign stats', [
                        'campaign_id' => $campaign->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Don't fail the transaction for stats update issues
                }

                // Notify clipper about reward transfer
                try {
                    $notificationService = app(\App\Services\NotificationService::class);
                    $notificationService->notifyRewardReceived($clip);
                } catch (Exception $e) {
                    Log::warning('Failed to send reward notification', [
                        'clip_id' => $clip->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Don't fail the transaction for notification issues
                }

                // Log successful transfer
                try {
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
                } catch (Exception $e) {
                    Log::warning('Failed to log transfer', [
                        'clip_id' => $clip->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Don't fail the transaction for logging issues
                }

                return true;
            });
        } catch (Exception $e) {
            Log::error('Reward transfer execution failed', [
                'clip_id' => $clip->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Deduct platform fee from amount.
     */
    public function deductPlatformFee(float $amount): float
    {
        // Use PlatformSetting for dynamic configuration (admin can change via UI)
        // Fallback to config file if setting doesn't exist yet
        $feePercent = \App\Models\PlatformSetting::get(
            'clipper_platform_fee_percent',
            config('clipper.platform_fee_percent', 5)
        );
        return round($amount * ((float) $feePercent / 100), 2);
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

