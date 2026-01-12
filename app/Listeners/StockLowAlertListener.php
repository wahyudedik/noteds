<?php

namespace App\Listeners;

use App\Events\StockLowAlert;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StockLowAlertListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(StockLowAlert $event): void
    {
        $product = $event->product;
        $seller = $product->seller;

        // Send notification to seller
        $seller->notify(new \App\Notifications\StockLowAlertNotification($product));
    }
}
