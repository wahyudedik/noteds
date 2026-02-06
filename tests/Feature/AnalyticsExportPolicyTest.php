<?php

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsExportPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        AnalyticsEvent::factory()->count(3)->create();
        $resp = $this->get('/analytics/events/export');
        $resp->assertStatus(200);
        $resp->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_analyst_can_export()
    {
        $user = User::factory()->create(['role' => 'analyst']);
        $this->actingAs($user);
        $resp = $this->get('/analytics/events/export');
        $resp->assertStatus(200);
    }

    public function test_viewer_cannot_export()
    {
        $user = User::factory()->create(['role' => 'viewer']);
        $this->actingAs($user);
        $resp = $this->get('/analytics/events/export');
        $resp->assertStatus(403);
    }
}
