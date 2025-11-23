<?php

namespace App\Services;

use App\Models\Escrow;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Dispute;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EscrowService
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }

    /**
     * Create escrow for transaction
     */
    public function createEscrow(
        Transaction $transaction,
        int $autoReleaseDays = 7,
        ?float $escrowFeePercent = null,
        ?float $platformFeePercent = null
    ): Escrow {
        // Calculate fees
        $escrowFeePercent = $escrowFeePercent ?? config('escrow.fee_percent', 0);
        $platformFeePercent = $platformFeePercent ?? config('escrow.platform_fee_percent', 0);
        
        $escrowFee = ($transaction->amount * $escrowFeePercent) / 100;
        $platformFee = ($transaction->amount * $platformFeePercent) / 100;

        $escrow = Escrow::create([
            'transaction_id' => $transaction->id,
            'buyer_id' => $transaction->buyer_id,
            'seller_id' => $transaction->seller_id,
            'note_id' => $transaction->note_id,
            'amount' => $transaction->amount,
            'escrow_fee' => $escrowFee,
            'platform_fee' => $platformFee,
            'status' => 'pending',
            'auto_release_days' => $autoReleaseDays,
            'auto_release_at' => now()->addDays($autoReleaseDays),
        ]);

        return $escrow;
    }

    /**
     * Fund escrow (deduct from buyer wallet)
     */
    public function fundEscrow(Escrow $escrow): void
    {
        if ($escrow->status !== 'pending') {
            throw new \Exception('Escrow is not in pending status.');
        }

        DB::transaction(function () use ($escrow) {
            $buyer = $escrow->buyer;

            // Check if buyer has sufficient balance
            if ($buyer->wallet_balance < $escrow->amount) {
                throw new \Exception('Insufficient wallet balance.');
            }

            // Deduct from buyer wallet
            $buyer->decrement('wallet_balance', $escrow->amount);

            // Sync Wallet model
            $wallet = \App\Models\Wallet::firstOrCreate(
                ['user_id' => $buyer->id],
                ['balance' => 0, 'currency' => config('currency.base_currency', 'IDR')]
            );
            $wallet->balance = $buyer->wallet_balance;
            $wallet->save();

            // Update escrow status
            $escrow->update([
                'status' => 'funded',
                'funded_at' => now(),
            ]);

            // Update transaction status
            $escrow->transaction->update(['status' => 'escrow_funded']);

            // Notify seller
            $this->notificationService->create(
                $escrow->seller,
                'escrow_funded',
                '💰 Escrow Funded',
                "Escrow for '{$escrow->note->title}' has been funded. Payment will be released after buyer confirmation or auto-release in {$escrow->auto_release_days} days.",
                route('escrows.show', $escrow),
                ['escrow_id' => $escrow->id, 'transaction_id' => $escrow->transaction_id]
            );

            // Notify buyer
            $this->notificationService->create(
                $buyer,
                'escrow_funded_buyer',
                '✅ Payment Held in Escrow',
                "Your payment for '{$escrow->note->title}' has been held in escrow. Confirm receipt to release payment to seller.",
                route('escrows.show', $escrow),
                ['escrow_id' => $escrow->id]
            );
        });
    }

    /**
     * Release escrow to seller
     */
    public function releaseEscrow(
        Escrow $escrow,
        User $releaser,
        ?string $notes = null
    ): void {
        if (!$escrow->isFunded()) {
            throw new \Exception('Escrow is not funded.');
        }

        if ($escrow->isDisputed()) {
            throw new \Exception('Cannot release disputed escrow. Please resolve dispute first.');
        }

        DB::transaction(function () use ($escrow, $releaser, $notes) {
            $seller = $escrow->seller;
            $payoutAmount = $escrow->getSellerPayoutAmount();

            // Add to seller wallet
            $seller->increment('wallet_balance', $payoutAmount);

            // Sync Wallet model
            $wallet = \App\Models\Wallet::firstOrCreate(
                ['user_id' => $seller->id],
                ['balance' => 0, 'currency' => config('currency.base_currency', 'IDR')]
            );
            $wallet->balance = $seller->wallet_balance;
            $wallet->save();

            // Update escrow status
            $escrow->update([
                'status' => 'released',
                'released_at' => now(),
                'released_by' => $releaser->id,
                'release_notes' => $notes,
            ]);

            // Update transaction status
            $escrow->transaction->update(['status' => 'success']);

            // Notify seller
            $this->notificationService->create(
                $seller,
                'escrow_released',
                '✅ Escrow Released',
                "Escrow for '{$escrow->note->title}' has been released. Amount: " . number_format($payoutAmount, 2) . " has been added to your wallet.",
                route('escrows.show', $escrow),
                ['escrow_id' => $escrow->id, 'amount' => $payoutAmount]
            );

            // Notify buyer
            $this->notificationService->create(
                $escrow->buyer,
                'escrow_released_buyer',
                '✅ Payment Released',
                "Payment for '{$escrow->note->title}' has been released to the seller.",
                route('escrows.show', $escrow),
                ['escrow_id' => $escrow->id]
            );
        });
    }

    /**
     * Refund escrow to buyer
     */
    public function refundEscrow(
        Escrow $escrow,
        User $refunder,
        string $reason
    ): void {
        if (!$escrow->isFunded()) {
            throw new \Exception('Escrow is not funded.');
        }

        DB::transaction(function () use ($escrow, $refunder, $reason) {
            $buyer = $escrow->buyer;

            // Refund to buyer wallet
            $buyer->increment('wallet_balance', $escrow->amount);

            // Sync Wallet model
            $wallet = \App\Models\Wallet::firstOrCreate(
                ['user_id' => $buyer->id],
                ['balance' => 0, 'currency' => config('currency.base_currency', 'IDR')]
            );
            $wallet->balance = $buyer->wallet_balance;
            $wallet->save();

            // Update escrow status
            $escrow->update([
                'status' => 'refunded',
                'refunded_at' => now(),
                'refunded_by' => $refunder->id,
                'refund_reason' => $reason,
            ]);

            // Update transaction status
            $escrow->transaction->update(['status' => 'refunded']);

            // Notify buyer
            $this->notificationService->create(
                $buyer,
                'escrow_refunded',
                '💰 Escrow Refunded',
                "Escrow for '{$escrow->note->title}' has been refunded. Amount: " . number_format($escrow->amount, 2) . " has been returned to your wallet. Reason: {$reason}",
                route('escrows.show', $escrow),
                ['escrow_id' => $escrow->id, 'amount' => $escrow->amount]
            );

            // Notify seller
            $this->notificationService->create(
                $escrow->seller,
                'escrow_refunded_seller',
                '⚠️ Escrow Refunded',
                "Escrow for '{$escrow->note->title}' has been refunded to the buyer. Reason: {$reason}",
                route('escrows.show', $escrow),
                ['escrow_id' => $escrow->id]
            );
        });
    }

    /**
     * Mark escrow as disputed
     */
    public function markAsDisputed(Escrow $escrow, Dispute $dispute): void
    {
        if (!$escrow->isFunded()) {
            throw new \Exception('Escrow is not funded.');
        }

        $escrow->update([
            'status' => 'disputed',
            'dispute_id' => $dispute->id,
        ]);

        // Notify both parties
        $this->notificationService->create(
            $escrow->buyer,
            'escrow_disputed',
            '⚠️ Escrow Disputed',
            "Escrow for '{$escrow->note->title}' has been marked as disputed. Payment is on hold until dispute is resolved.",
            route('escrows.show', $escrow),
            ['escrow_id' => $escrow->id, 'dispute_id' => $dispute->id]
        );

        $this->notificationService->create(
            $escrow->seller,
            'escrow_disputed',
            '⚠️ Escrow Disputed',
            "Escrow for '{$escrow->note->title}' has been marked as disputed. Payment is on hold until dispute is resolved.",
            route('escrows.show', $escrow),
            ['escrow_id' => $escrow->id, 'dispute_id' => $dispute->id]
        );
    }

    /**
     * Auto-release escrows that are past auto-release date
     */
    public function autoReleaseEscrows(): int
    {
        $escrows = Escrow::where('status', 'funded')
            ->where('auto_release_at', '<=', now())
            ->whereNull('dispute_id')
            ->get();

        $released = 0;

        foreach ($escrows as $escrow) {
            try {
                // Auto-release as buyer (since buyer didn't confirm)
                $this->releaseEscrow(
                    $escrow,
                    $escrow->buyer,
                    'Auto-released after ' . $escrow->auto_release_days . ' days'
                );
                $released++;
            } catch (\Exception $e) {
                Log::error('Failed to auto-release escrow', [
                    'escrow_id' => $escrow->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $released;
    }

    /**
     * Get escrow configuration
     */
    public function getConfig(): array
    {
        return [
            'enabled' => config('escrow.enabled', true),
            'auto_release_days' => config('escrow.auto_release_days', 7),
            'escrow_fee_percent' => config('escrow.fee_percent', 0),
            'platform_fee_percent' => config('escrow.platform_fee_percent', 0),
        ];
    }
}

