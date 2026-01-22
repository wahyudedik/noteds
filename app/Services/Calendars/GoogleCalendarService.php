<?php

namespace App\Services\Calendars;

use App\Models\GroupEvent;

class GoogleCalendarService
{
    public function syncEvent(GroupEvent $event): bool
    {
        return false;
    }

    public function removeEvent(GroupEvent $event): bool
    {
        return false;
    }
}
