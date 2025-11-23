<?php

namespace App\Services;

use App\Models\Refund;
use App\Models\Transaction;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class RefundService
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }

    /**
     * Approve and process refund
     */
    public function approveRefund(Refund $refund, User $processor): void
    {
        DB::transaction(function () use ($refund, $processor) {
            // Update refund status
            $refund->update([
                'status' => 'approved',
                'processed_by' => $processor->id,
                'processed_at' => now(),
            ]);

            // Process refund: Add amount back to buyer's wallet
            $buyer = $refund->buyer;
            $buyer->increment('wallet_balance', $refund->amount);

            // Sync Wallet model
            $wallet = \App\Models\Wallet::firstOrCreate(
                ['user_id' => $buyer->id],
                ['balance' => 0, 'currency' => config('currency.base_currency', 'IDR')]
            );
            $wallet->balance = $buyer->wallet_balance;
            $wallet->save();

            // Deduct from seller's wallet (if they still have balance)
            $seller = $refund->seller;
            if ($seller->wallet_balance >= $refund->amount) {
                $seller->decrement('wallet_balance', $refund->amount);
                
                $sellerWallet = \App\Models\Wallet::firstOrCreate(
                    ['user_id' => $seller->id],
                    ['balance' => 0, 'currency' => config('currency.base_currency', 'IDR')]
                );
                $sellerWallet->balance = $seller->wallet_balance;
                $sellerWallet->save();
            }

            // Update refund status to processed
            $refund->update(['status' => 'processed']);

            // Notify buyer
            $this->notificationService->create(
                $buyer,
                'refund_approved',
                '✅ Refund Approved',
                'Your refund request for ' . $refund->note->title . ' has been approved. Amount: Rp ' . number_format($refund->amount, 0, ',', '.') . ' has been added to your wallet.',
                route('refunds.show', $refund),
                ['refund_id' => $refund->id, 'amount' => $refund->amount]
            );

            // Notify seller
            $this->notificationService->create(
                $seller,
                'refund_approved_seller',
                '💰 Refund Processed',
                'A refund has been processed for: ' . $refund->note->title . '. Amount: Rp ' . number_format($refund->amount, 0, ',', '.') . ' has been deducted from your wallet.',
                route('refunds.show', $refund),
                ['refund_id' => $refund->id]
            );
        });
    }
}

