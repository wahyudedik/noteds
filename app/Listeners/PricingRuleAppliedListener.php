<?php

namespace App\Listeners;

use App\Events\PricingRuleApplied;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PricingRuleAppliedListener implements ShouldQueue
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
    public function handle(PricingRuleApplied $event): void
    {
        // Log rule application for analytics
        // Could add additional logic here if needed
    }
}
