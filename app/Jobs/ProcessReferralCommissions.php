<?php

namespace App\Jobs;

use App\Models\Referral;
use App\Models\Setting;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessReferralCommissions implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Check if automatic sending is enabled
            $isEnabled = (bool) Setting::getSetting('referral_auto_send_enabled', 'referral', false);
            if (!$isEnabled) {
                Log::info('Referral commission processing skipped - automatic sending disabled');
                return;
            }

            Log::info('Starting referral commission processing job');

            $referralService = app(ReferralService::class);
            $admin = User::role('admin')->first();

            if (!$admin) {
                Log::warning('No admin user found for referral commission processing');
                return;
            }

            // Get pending commissions
            $pendingCommissions = Referral::where('status', 'pending')
                ->with('referrer', 'referred')
                ->get();

            if ($pendingCommissions->isEmpty()) {
                Log::info('No pending referral commissions to process');
                return;
            }

            $minAmount = (float) Setting::getSetting('referral_min_amount_to_send', 'referral', 0);
            $maxBatch = (int) Setting::getSetting('referral_max_batch_size', 'referral', 100);

            // Filter by minimum amount
            $commissions = $pendingCommissions
                ->filter(fn($r) => $r->reward_amount >= $minAmount)
                ->take($maxBatch);

            $totalAmount = $commissions->sum('reward_amount');

            // Validate admin balance
            if (!$referralService->validateAdminBalance($totalAmount)) {
                Log::warning('Insufficient admin balance for processing commissions', [
                    'required' => $totalAmount,
                    'available' => $admin->wallet_balance,
                ]);
                return;
            }

            $processed = 0;
            $failed = 0;
            $sentNotifications = [];

            foreach ($commissions as $referral) {
                $transaction = $referralService->processCommission($referral, $admin);

                if ($transaction) {
                    $processed++;

                    // Collect notification data
                    $sentNotifications[] = [
                        'user_id' => $transaction->user_id,
                        'amount' => (float) $transaction->amount,
                        'type' => $transaction->type,
                        'admin_id' => $admin->id,
                    ];
                } else {
                    $failed++;
                }
            }

            // Send notifications to admin
            if ($processed > 0) {
                Log::info("Referral commissions processed", [
                    'total_processed' => $processed,
                    'total_failed' => $failed,
                    'total_amount' => $totalAmount,
                ]);

                // Send batch notification to admin
                $admin->notify(new \App\Notifications\ReferralCommissionSentNotification(
                    $processed,
                    $totalAmount,
                    $failed
                ));
            }

            // Send notifications to each user
            foreach ($sentNotifications as $notification) {
                $user = User::find($notification['user_id']);
                if ($user) {
                    $user->notify(new \App\Notifications\ReferralCommissionReceivedNotification(
                        $notification['amount'],
                        $notification['type']
                    ));
                }
            }
        } catch (\Exception $e) {
            Log::error('Error processing referral commissions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
