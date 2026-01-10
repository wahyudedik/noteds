<?php

namespace App\Jobs;

use App\Services\SubscriptionService;
use App\Models\ProductSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSubscriptionRenewals implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private ProductSubscription $subscription
    ) {}

    public function handle(SubscriptionService $subscriptionService): void
    {
        try {
            $subscriptionService->renewSubscription($this->subscription);
        } catch (\Exception $e) {
            Log::error("Failed to process subscription renewal: {$this->subscription->id}", [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
