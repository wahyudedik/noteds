<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessibilityPreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_requires_auth(): void
    {
        $res = $this->getJson('/api/user/a11y/preferences');
        $res->assertStatus(401);
    }

    public function test_save_and_get_preferences(): void
    {
        $user = User::factory()->create();
        $payload = ['reduce_motion' => 'medium', 'high_contrast' => true, 'font_scale' => 150, 'keyboard_navigation' => true];
        $this->actingAs($user)->postJson('/api/user/a11y/preferences', $payload)->assertStatus(200);
        $get = $this->actingAs($user)->getJson('/api/user/a11y/preferences')->assertStatus(200)->json();
        $this->assertEquals('medium', $get['preferences']['reduce_motion'] ?? null);
        $this->assertTrue($get['preferences']['high_contrast'] ?? false);
        $this->assertEquals(150, $get['preferences']['font_scale'] ?? 0);
        $this->assertTrue($get['preferences']['keyboard_navigation'] ?? false);
    }

    public function test_report_store(): void
    {
        $user = User::factory()->create();
        $report = ['violations' => [['id' => 'color-contrast'], ['id' => 'aria-roles']]];
        $this->actingAs($user)->postJson('/api/a11y/report', ['context' => '/test', 'report' => $report])->assertStatus(200);
        $this->assertDatabaseHas('a11y_reports', ['context' => '/test']);
    }
}
