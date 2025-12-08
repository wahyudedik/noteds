<?php

namespace App\Jobs;

use App\Events\ShareCommissionPaid;
use App\Mail\ShareCommissionPaidMail;
use App\Models\NoteShareCommission;
use App\Models\Setting;
use App\Models\Wallet;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ProcessMonthlyShareCommissionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct() {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get last month in Y-m format
        $month = now()->subMonth()->format('Y-m');

        // Get all pending commissions for last month
        $pendingCommissions = NoteShareCommission::where('month', $month)
            ->where('status', 'pending')
            ->get();

        if ($pendingCommissions->isEmpty()) {
            logger()->info('No pending share commissions to process for month: ' . $month);
            return;
        }

        // Group by seller
        $commissionsBySeller = $pendingCommissions->groupBy('seller_id');
        $baseCurrency = config('currency.base_currency', 'IDR');
        $adminUser = User::where('role', 'admin')->firstOrFail();

        try {
            DB::beginTransaction();

            foreach ($commissionsBySeller as $sellerId => $sellerCommissions) {
                $totalAmount = $sellerCommissions->sum('commission_amount');
                $shareCount = $sellerCommissions->sum('share_count');
                $seller = User::find($sellerId);

                if (!$seller) {
                    logger()->warning('Seller not found for commission transfer: ' . $sellerId);
                    continue;
                }

                // Deduct from admin wallet
                $adminUser->decrement('wallet_balance', $totalAmount);

                // Transfer to seller wallet
                $seller->increment('wallet_balance', $totalAmount);

                // Sync wallets
                $adminWallet = Wallet::firstOrCreate(
                    ['user_id' => $adminUser->id],
                    ['balance' => 0, 'currency' => $baseCurrency]
                );
                $adminWallet->balance = $adminUser->wallet_balance;
                $adminWallet->save();

                $sellerWallet = Wallet::firstOrCreate(
                    ['user_id' => $seller->id],
                    ['balance' => 0, 'currency' => $baseCurrency]
                );
                $sellerWallet->balance = $seller->wallet_balance;
                $sellerWallet->save();

                // Mark commissions as paid
                foreach ($sellerCommissions as $commission) {
                    $commission->markAsPaid();
                }

                // Send email notification
                if ($seller->email) {
                    try {
                        Mail::to($seller->email)->queue(
                            new ShareCommissionPaidMail(
                                $seller,
                                $totalAmount,
                                $month,
                                $sellerCommissions->count()
                            )
                        );
                    } catch (\Exception $e) {
                        logger()->error('Failed to send commission payment email', [
                            'seller_id' => $sellerId,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                // Dispatch event for broadcast notification
                event(new ShareCommissionPaid(
                    $seller,
                    $totalAmount,
                    $month,
                    $sellerCommissions->count()
                ));

                logger()->info('Transferred share commissions to seller', [
                    'seller_id' => $sellerId,
                    'month' => $month,
                    'total_amount' => $totalAmount,
                    'commission_count' => $sellerCommissions->count(),
                ]);
            }

            DB::commit();

            logger()->info('Monthly share commission processing completed', [
                'month' => $month,
                'sellers_processed' => count($commissionsBySeller),
                'total_commissions' => $pendingCommissions->count(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Monthly share commission processing failed', [
                'month' => $month,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
