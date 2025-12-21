<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\MarketplaceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateLicenseKey implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

    public function handle(MarketplaceService $marketplaceService): void
    {
        if ($this->order->product->license_key) {
            $licenseKey = $marketplaceService->generateLicenseKey($this->order);
            $this->order->update(['license_key' => $licenseKey]);
        }
    }
}
