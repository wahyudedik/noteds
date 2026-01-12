<?php

namespace App\Listeners;

use App\Events\SellerVerified;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SellerVerifiedListener implements ShouldQueue
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
    public function handle(SellerVerified $event): void
    {
        $seller = $event->seller;

        // Send notification to seller
        $seller->notify(new \App\Notifications\SellerVerifiedNotification($seller));
    }
}
