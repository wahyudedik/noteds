<?php

namespace App\Console\Commands;

use App\Jobs\ProcessSubscriptionRenewal;
use App\Models\BuyerSubscription;
use Illuminate\Console\Command;

class ProcessSubscriptionRenewals extends Command
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
    protected $description = 'Process subscription renewals for active subscriptions';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Processing subscription renewals...');

        // Find subscriptions that need renewal (next_billing_date is today or past)
        $subscriptions = BuyerSubscription::where('status', 'active')
            ->where('auto_renew', true)
            ->whereNull('cancelled_at')
            ->where('next_billing_date', '<=', now())
            ->with(['user', 'plan'])
            ->get();

        $this->info("Found {$subscriptions->count()} subscriptions to renew.");

        foreach ($subscriptions as $subscription) {
            ProcessSubscriptionRenewal::dispatch($subscription);
            $this->line("Dispatched renewal job for subscription {$subscription->id} (User: {$subscription->user->email})");
        }

        $this->info('Subscription renewal processing complete.');
    }
}
