<?php

namespace App\Listeners;

use App\Events\SellerRatingUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SellerRatingUpdatedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SellerRatingUpdated $event): void
    {
        // Could add additional logic here if needed
        // e.g., cache invalidation, analytics tracking
    }
}
