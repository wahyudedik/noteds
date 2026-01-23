<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_events_endpoint_requires_auth(): void
    {
        $this->get('/api/calendar/events?from=' . now()->toIso8601String() . '&to=' . now()->addDay()->toIso8601String())
            ->assertRedirect('/login');
    }

    public function test_user_can_create_event_and_list_it(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $payload = [
            'title' => 'Test Event',
            'privacy' => 'public',
            'start_at' => now()->addDay()->toIso8601String(),
            'end_at' => now()->addDay()->addHour()->toIso8601String(),
            'timezone' => 'UTC',
        ];
        $this->post('/api/calendar/events', $payload)->assertStatus(200);

        $from = now()->toIso8601String();
        $to = now()->addDays(2)->toIso8601String();
        $this->get('/api/calendar/events?from=' . $from . '&to=' . $to)
            ->assertStatus(200)
            ->assertSee('Test Event');
    }

    public function test_user_can_update_event_time(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $event = Event::factory()->create([
            'user_id' => $user->id,
            'title' => 'Resize Event',
            'privacy' => 'public',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'timezone' => 'UTC',
        ]);

        $this->put('/api/calendar/events/' . $event->id, [
            'end_at' => now()->addDay()->addHours(2)->toIso8601String()
        ])->assertStatus(200);
    }

    public function test_export_pdf_works(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $event = Event::factory()->create([
            'user_id' => $user->id,
            'title' => 'Exported Event',
            'privacy' => 'public',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'timezone' => 'UTC',
        ]);

        $this->get('/api/calendar/export?from=' . now()->toIso8601String() . '&to=' . now()->addDays(2)->toIso8601String() . '&view=week')
            ->assertStatus(200);
    }
}
