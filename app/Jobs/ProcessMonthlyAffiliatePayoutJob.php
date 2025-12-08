<?php

namespace App\Jobs;

use App\Models\AffiliateCommission;
use App\Models\AffiliatePayout;
use App\Models\Setting;
use App\Models\User;
use App\Mail\AffiliatePayoutProcessedMail;
use App\Events\AffiliatePayoutProcessed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ProcessMonthlyAffiliatePayoutJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes

    public function __construct()
    {
        $this->queue = 'default';
    }

    public function handle(): void
    {
        try {
            Log::info('Processing monthly affiliate payouts...');

            // Get unpaid commissions grouped by affiliate
            $unpaidCommissions = AffiliateCommission::where('status', 'pending')
                ->select('affiliate_id')
                ->distinct()
                ->pluck('affiliate_id');

            if ($unpaidCommissions->isEmpty()) {
                Log::info('No unpaid affiliate commissions to process');
                return;
            }

            foreach ($unpaidCommissions as $affiliateId) {
                $this->processAffiliatePayouts($affiliateId);
            }

            Log::info('Monthly affiliate payouts processed successfully');
        } catch (\Exception $e) {
            Log::error('Error processing monthly affiliate payouts: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * Process payouts for a single affiliate
     */
    private function processAffiliatePayouts(int $affiliateId): void
    {
        $affiliate = User::find($affiliateId);
        if (!$affiliate) {
            Log::warning("Affiliate user not found: {$affiliateId}");
            return;
        }

        try {
            // Get total unpaid commissions for this affiliate
            $totalCommission = AffiliateCommission::where('affiliate_id', $affiliateId)
                ->where('status', 'pending')
                ->sum('commission_amount');

            if ($totalCommission <= 0) {
                return;
            }

            // Get min payout amount setting
            $minPayoutAmount = Setting::getSetting('affiliate_min_payout_amount', 'marketplace', 50);

            // Check if meets minimum
            if ($totalCommission < $minPayoutAmount) {
                Log::info(
                    "Affiliate {$affiliate->username} commission {$totalCommission} below minimum {$minPayoutAmount}",
                    ['affiliate_id' => $affiliateId]
                );
                return;
            }

            // Get admin wallet (platform account)
            $adminUser = User::whereHas('roles', function ($q) {
                $q->where('name', 'admin');
            })->first();

            if (!$adminUser || $adminUser->wallet->balance < $totalCommission) {
                Log::warning(
                    "Admin wallet insufficient for affiliate payout",
                    ['affiliate_id' => $affiliateId, 'amount' => $totalCommission]
                );
                return;
            }

            // Create automatic payout request
            $payout = AffiliatePayout::create([
                'affiliate_id' => $affiliateId,
                'amount' => $totalCommission,
                'payout_method' => 'wallet',
                'status' => 'pending',
            ]);

            // Deduct from admin wallet
            $adminUser->wallet()->decrement('balance', $totalCommission);

            // Add to affiliate wallet
            $affiliate->wallet()->increment('balance', $totalCommission);

            // Mark commissions as paid
            AffiliateCommission::where('affiliate_id', $affiliateId)
                ->where('status', 'pending')
                ->update(['status' => 'paid']);

            // Update payout status to completed
            $payout->update(['status' => 'completed']);

            // Send notification
            try {
                Mail::queue(new AffiliatePayoutProcessedMail(
                    $payout,
                    'completed',
                    'Monthly automatic payout processed'
                ));

                event(new AffiliatePayoutProcessed(
                    $payout,
                    'completed',
                    'Monthly automatic payout processed'
                ));
            } catch (\Exception $e) {
                Log::error("Error sending payout notification: " . $e->getMessage());
            }

            Log::info(
                "Affiliate payout processed successfully",
                [
                    'affiliate_id' => $affiliateId,
                    'amount' => $totalCommission,
                    'payout_id' => $payout->id,
                ]
            );
        } catch (\Exception $e) {
            Log::error("Error processing affiliate payout: " . $e->getMessage(), [
                'affiliate_id' => $affiliateId,
                'exception' => $e,
            ]);
        }
    }
}
