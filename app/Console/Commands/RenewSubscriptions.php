<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Setting;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RenewSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:renew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-renew subscriptions that are expiring or expired. Deduct from wallet or expire if insufficient balance.';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService)
    {
        $this->info('Starting subscription renewal process...');

        $premiumPrice = Setting::getPremiumPrice();
        
        // Get active subscriptions that are expiring today or already expired
        // Check subscriptions that expired today or will expire tomorrow (for early renewal)
        $expiringSubscriptions = Subscription::where('status', 'active')
            ->where('plan', 'premium')
            ->where(function ($query) {
                $query->whereDate('expired_at', '<=', now())
                    ->orWhereDate('expired_at', '=', now()->addDay());
            })
            ->with('user')
            ->get();

        $this->info("Found {$expiringSubscriptions->count()} subscriptions to process.");

        $renewed = 0;
        $expired = 0;
        $errors = 0;
        $reminders = 0;

        $currencyService = app(\App\Services\CurrencyService::class);
        $baseCurrency = $currencyService->getBaseCurrency();

        foreach ($expiringSubscriptions as $subscription) {
            try {
                $user = $subscription->user;
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => 0, 'currency' => $baseCurrency]
                );
                if ($wallet->currency !== $baseCurrency) {
                    $wallet->currency = $baseCurrency;
                    $wallet->save();
                }

                // Sync wallet balance
                if ($wallet->balance != $user->wallet_balance) {
                    $wallet->balance = $user->wallet_balance;
                    $wallet->save();
                }

                $expiresTomorrow = $subscription->expired_at && $subscription->expired_at->isSameDay(now()->addDay());

                if ($expiresTomorrow && $wallet->balance < $premiumPrice) {
                    $notificationService->notifySubscriptionRenewalReminder($user, $premiumPrice, $wallet->balance);
                    $reminders++;
                    $this->info("ℹ︎ Sent renewal reminder to {$user->email} (balance insufficient)");
                    continue;
                }

                // Check if wallet has sufficient balance
                if ($wallet->balance >= $premiumPrice) {
                    // Auto-renew subscription
                    DB::transaction(function () use ($subscription, $user, $wallet, $premiumPrice, $notificationService, $baseCurrency) {
                        // Deduct from wallet
                        $wallet->balance -= $premiumPrice;
                        $wallet->save();

                        // Update user wallet_balance
                        $user->wallet_balance = $wallet->balance;
                        $user->save();

                        // Create transaction record
                        Transaction::create([
                            'buyer_id' => $user->id,
                            'seller_id' => $user->id,
                            'note_id' => null,
                            'amount' => $premiumPrice,
                            'commission' => 0,
                            'currency' => $baseCurrency,
                            'original_amount' => $premiumPrice,
                            'original_currency' => $baseCurrency,
                            'exchange_rate' => 1,
                            'status' => 'success',
                            'payment_method' => 'wallet',
                            'notes' => 'Premium subscription auto-renewal',
                        ]);

                        // Extend subscription
                        $subscription->expired_at = now()->addMonth();
                        $subscription->save();

                        // Send success notification
                        $notificationService->notifySubscriptionRenewed($user, $premiumPrice);
                        $notificationService->maybeNotifyLowBalance($user, $wallet->balance);
                    });

                    $renewed++;
                    $this->info("✓ Renewed subscription for user: {$user->email}");
                } else {
                    // Insufficient balance - expire subscription
                    $currentBalance = $wallet->balance;
                    DB::transaction(function () use ($subscription, $user, $premiumPrice, $currentBalance, $notificationService) {
                        // Update subscription status to expired
                        $subscription->status = 'expired';
                        $subscription->save();

                        // Send email notification
                        try {
                            Mail::to($user->email)->send(
                                new \App\Mail\SubscriptionRenewalFailedMail($user, $premiumPrice, $currentBalance)
                            );
                        } catch (\Exception $e) {
                            Log::error('Failed to send subscription renewal failure email: ' . $e->getMessage());
                        }

                        // Send app notification
                        $notificationService->notifySubscriptionExpired($user, $premiumPrice, $currentBalance);
                        $notificationService->maybeNotifyLowBalance($user, $currentBalance);
                    });

                    $expired++;
                    $this->warn("✗ Expired subscription for user: {$user->email} (insufficient balance)");
                }
            } catch (\Exception $e) {
                $errors++;
                Log::error("Subscription renewal error for user {$subscription->user_id}: " . $e->getMessage());
                $this->error("✗ Error processing subscription for user ID: {$subscription->user_id} - " . $e->getMessage());
            }
        }

        $this->info("\n=== Renewal Summary ===");
        $this->info("Renewed: {$renewed}");
        $this->info("Expired (insufficient balance): {$expired}");
        $this->info("Reminders sent: {$reminders}");
        $this->info("Errors: {$errors}");

        return Command::SUCCESS;
    }
}
