<?php

namespace App\Services;

use Sentry\Event;

class SentryBeforeSendCallback
{
    /**
     * Handle the before_send callback for Sentry.
     *
     * @param Event $event
     * @return Event|null
     */
    public function __invoke(Event $event): ?Event
    {
        // Filter out sensitive data or modify event before sending
        // Example: Remove sensitive information from context
        
        // You can customize this based on your needs
        // For example, filter out specific exceptions or modify event data
        
        return $event;
    }
}

