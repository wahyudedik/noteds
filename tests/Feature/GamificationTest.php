<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\GamificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GamificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_login_awards_points_once(): void
    {
        $user = User::factory()->create();
        $service = app(GamificationService::class);

        $this->actingAs($user);
        $service->awardDailyLogin($user);
        $first = $service->getUserTotalPoints($user, 'daily');
        $service->awardDailyLogin($user);
        $second = $service->getUserTotalPoints($user, 'daily');
        $this->assertGreaterThan(0, $first);
        $this->assertEquals($first, $second);
    }

    public function test_leaderboard_returns_data(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $service = app(GamificationService::class);
        $service->awardPoints($user1, 'test_action', 50);
        $service->awardPoints($user2, 'test_action', 10);

        $resp = $this->actingAs($user1)->get('/api/gamification/leaderboard?period=daily&limit=10');
        $resp->assertStatus(200);
        $this->assertStringContainsString('data', $resp->getContent());
    }
}
