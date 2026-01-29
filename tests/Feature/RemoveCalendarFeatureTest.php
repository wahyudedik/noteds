<?php

namespace Tests\Feature;

use Tests\TestCase;

class RemoveCalendarFeatureTest extends TestCase
{
    public function test_calendar_routes_return_410()
    {
        $this->withoutMiddleware();
        $this->get('/calendar')->assertStatus(410);
        $this->get('/api/calendar/events')->assertStatus(410);
        $this->post('/api/calendar/events')->assertStatus(410);
        $this->put('/api/calendar/events/any')->assertStatus(410);
        $this->get('/api/calendar/export')->assertStatus(410);
        $this->get('/api/scheduling/calendar')->assertStatus(410);
        $this->get('/events/calendar')->assertStatus(410);
        $this->get('/groups/group-slug/events/calendar')->assertStatus(410);
    }
}
