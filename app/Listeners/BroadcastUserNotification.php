<?php

namespace App\Listeners;

use App\Events\UserNotificationCreated;
use App\Models\User;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Notifications\DatabaseNotification;

class BroadcastUserNotification
{
    /**
     * Handle the event.
     */
    public function handle(NotificationSent $event): void
    {
        // We only care about database notifications for real-time in-app updates
        if ($event->channel !== 'database') {
            return;
        }

        $notifiable = $event->notifiable;

        if (! $notifiable instanceof User) {
            return;
        }

        $response = $event->response;

        if (! $response instanceof DatabaseNotification) {
            return;
        }

        $settings = $notifiable->settings;
        $preferences = $settings?->notification_preferences ?? [];

        $playSound = (bool) ($preferences['sound_enabled'] ?? true);

        event(new UserNotificationCreated($response, $playSound));
    }
}


