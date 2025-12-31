<?php

namespace App\Services;

use App\Models\User;
use App\Models\TopUp;
use App\Models\CreatorWallet;
use App\Services\WalletService;
use App\Services\MidtransService;
use Illuminate\Support\Facades\DB;

class TopUpService
{
    public function __construct(
        protected WalletService $walletService,
        protected MidtransService $midtransService
    ) {}

    /**
     * Create top up request.
     */
    public function createTopUp(User $user, float $amount, string $paymentMethod): TopUp
    {
        $topUp = TopUp::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'status' => 'pending_payment',
            'payment_method' => $paymentMethod,
        ]);

        // Create Midtrans transaction
        $params = [
            'transaction_details' => [
                'order_id' => 'TOPUP-' . $topUp->id,
                'gross_amount' => (int) $amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'item_details' => [
                [
                    'id' => 'topup',
                    'price' => (int) $amount,
                    'quantity' => 1,
                    'name' => 'Top Up Wallet',
                ],
            ],
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            
            $topUp->update([
                'midtrans_order_id' => 'TOPUP-' . $topUp->id,
            ]);

            return $topUp;
        } catch (\Exception $e) {
            $topUp->markAsFailed();
            throw $e;
        }
    }

    /**
     * Process top up success.
     */
    public function processTopUpSuccess(TopUp $topUp): bool
    {
        return DB::transaction(function () use ($topUp) {
            if ($topUp->status === 'success') {
                return true; // Already processed
            }

            // Mark top up as paid
            $topUp->markAsPaid();

            // Add to creator wallet
            $this->addToCreatorWallet($topUp->user, $topUp->amount);

            // Create ledger entry
            $creatorWallet = $this->walletService->getCreatorWallet($topUp->user);
            \App\Models\LedgerEntry::createEntry([
                'from_wallet_type' => 'platform',
                'from_wallet_id' => null,
                'to_wallet_type' => 'creator',
                'to_wallet_id' => $creatorWallet->id,
                'amount' => $topUp->amount,
                'reason' => 'topup',
                'reference_type' => 'topup',
                'reference_id' => $topUp->id,
            ]);

            return true;
        });
    }

    /**
     * Add amount to creator wallet.
     */
    public function addToCreatorWallet(User $user, float $amount): bool
    {
        $creatorWallet = $this->walletService->getCreatorWallet($user);
        return $creatorWallet->addBalance($amount);
    }
}

