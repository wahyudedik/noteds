<?php

namespace Tests\Feature;

use App\Events\UserNotificationCreated;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UserNotificationPreferencesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Simple database-only notification for testing.
     */
    private function makeTestNotification(): Notification
    {
        return new class extends Notification {
            public function via(object $notifiable): array
            {
                return ['database'];
            }

            public function toArray(object $notifiable): array
            {
                return [
                    'type' => 'test_notification',
                    'title' => 'Test Notification',
                    'message' => 'This is a test notification.',
                ];
            }
        };
    }

    public function test_in_app_notifications_can_be_globally_disabled(): void
    {
        $user = User::factory()->create();

        UserSetting::create([
            'user_id' => $user->id,
            'notification_preferences' => [
                'in_app_notifications' => false,
                'email_notifications' => true,
            ],
            'privacy_settings' => [],
            'email_preferences' => [],
            'profile_visibility' => true,
            'search_visibility' => true,
        ]);

        $user->notify($this->makeTestNotification());

        $this->assertSame(0, $user->notifications()->count(), 'No in-app notifications should be stored when disabled.');
    }

    public function test_sound_preference_is_respected_when_broadcasting(): void
    {
        $user = User::factory()->create();

        UserSetting::create([
            'user_id' => $user->id,
            'notification_preferences' => [
                'in_app_notifications' => true,
                'email_notifications' => true,
                'sound_enabled' => false,
            ],
            'privacy_settings' => [],
            'email_preferences' => [],
            'profile_visibility' => true,
            'search_visibility' => true,
        ]);

        Event::fake([UserNotificationCreated::class]);

        $user->notify($this->makeTestNotification());

        Event::assertDispatched(UserNotificationCreated::class, function (UserNotificationCreated $event) use ($user) {
            $this->assertInstanceOf(PrivateChannel::class, $event->broadcastOn());
            $this->assertSame('private-user.' . $user->id . '.notifications', $event->broadcastOn()->name);

            return $event->playSound === false;
        });
    }
}
