<?php

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OrderStatusUpdatedListener implements ShouldQueue
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
    public function handle(OrderStatusUpdated $event): void
    {
        // Event broadcasting is handled automatically by Laravel when ShouldBroadcast is implemented
        // Additional notification logic can be added here if needed
        
        // Optionally send notification to user about status update
        // $this->notificationService->notifyOrderStatusUpdated($event->order, $event->trackingHistory);
    }
}
