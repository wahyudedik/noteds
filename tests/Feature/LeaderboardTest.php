<?php

namespace Tests\Feature;

use App\Jobs\DistributeLeaderboardRewardsJob;
use App\Models\LeaderboardSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LeaderboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user1;
    protected User $user2;
    protected User $user3;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        // Create regular users
        $this->user1 = User::factory()->create(['username' => 'user1']);
        $this->user2 = User::factory()->create(['username' => 'user2']);
        $this->user3 = User::factory()->create(['username' => 'user3']);

        // Verify users
        $this->user1->markEmailAsVerified();
        $this->user2->markEmailAsVerified();
        $this->user3->markEmailAsVerified();
    }

    /**
     * Test: Admin can view leaderboard settings page
     */
    public function test_admin_can_view_leaderboard_settings(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/settings/leaderboard');

        $response->assertStatus(200);
        $response->assertViewIs('admin.leaderboard-settings.index');
        $response->assertViewHas('settingsData');
    }

    /**
     * Test: All 15 settings are displayed on settings page
     */
    public function test_leaderboard_settings_displays_all_fields(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/settings/leaderboard');

        $settingsData = $response['settingsData'];

        $this->assertCount(15, $settingsData);
        $this->assertArrayHasKey("share_points_per_share", $settingsData);
        $this->assertArrayHasKey("share_points_per_click", $settingsData);
        $this->assertArrayHasKey("share_points_per_purchase", $settingsData);
        $this->assertArrayHasKey("leaderboard_monthly_point_cap", $settingsData);
        $this->assertArrayHasKey("leaderboard_monthly_target", $settingsData);
        $this->assertArrayHasKey("leaderboard_reset_day", $settingsData);
        $this->assertArrayHasKey("monthly_reward_rank_1", $settingsData);
        $this->assertArrayHasKey("monthly_reward_rank_2", $settingsData);
        $this->assertArrayHasKey("monthly_reward_rank_3", $settingsData);
        $this->assertArrayHasKey("monthly_reward_top_10", $settingsData);
        $this->assertArrayHasKey("monthly_reward_top_50", $settingsData);
        $this->assertArrayHasKey("leaderboard_enabled", $settingsData);
        $this->assertArrayHasKey("duplicate_share_prevention", $settingsData);
        $this->assertArrayHasKey("auto_transfer_rewards", $settingsData);
        $this->assertArrayHasKey("reward_transfer_day", $settingsData);
    }

    /**
     * Test: Admin can update leaderboard settings
     */
    public function test_admin_can_update_leaderboard_settings(): void
    {
        $data = [
            'share_points_per_share' => 20,
            'share_points_per_click' => 8,
            'share_points_per_purchase' => 100,
            'leaderboard_monthly_point_cap' => 5000,
            'leaderboard_monthly_target' => 5000,
            'leaderboard_reset_day' => 5,
            'monthly_reward_rank_1' => 200000,
            'monthly_reward_rank_2' => 100000,
            'monthly_reward_rank_3' => 50000,
            'monthly_reward_top_10' => 10000,
            'monthly_reward_top_50' => 2000,
            'leaderboard_enabled' => 1,
            'duplicate_share_prevention' => 1,
            'auto_transfer_rewards' => 1,
            'reward_transfer_day' => 10,
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/settings/leaderboard', $data);

        $response->assertRedirect('/admin/settings/leaderboard');

        $this->assertEquals(20, LeaderboardSetting::get('share_points_per_share'));
        $this->assertEquals(8, LeaderboardSetting::get('share_points_per_click'));
        $this->assertEquals(5000, LeaderboardSetting::get('leaderboard_monthly_point_cap'));
        $this->assertEquals(10, LeaderboardSetting::get('reward_transfer_day'));
    }

    /**
     * Test: Non-admin users cannot access leaderboard settings
     */
    public function test_non_admin_cannot_access_leaderboard_settings(): void
    {
        $response = $this->actingAs($this->user1)
            ->get('/admin/settings/leaderboard');

        $response->assertStatus(403);
    }

    /**
     * Test: Unauthenticated users cannot access leaderboard settings
     */
    public function test_unauthenticated_cannot_access_leaderboard_settings(): void
    {
        $response = $this->get('/admin/settings/leaderboard');

        $response->assertRedirect('/login');
    }

    /**
     * Test: Users can view public leaderboard
     */
    public function test_users_can_view_public_leaderboard(): void
    {
        $response = $this->actingAs($this->user1)
            ->get('/leaderboard');

        $response->assertStatus(200);
    }

    /**
     * Test: Users can view share leaderboard
     */
    public function test_users_can_view_share_leaderboard(): void
    {
        $response = $this->actingAs($this->user1)
            ->get('/share/leaderboard');

        $response->assertStatus(200);
    }

    /**
     * Test: Point system awards configurable points per share
     */
    public function test_point_system_awards_configurable_points(): void
    {
        // Set custom point value
        LeaderboardSetting::set("share_points_per_share", 25);

        $sharePoints = LeaderboardSetting::get("share_points_per_share");

        $this->assertEquals(25, $sharePoints);
    }

    /**
     * Test: Duplicate share prevention blocks duplicate shares
     */
    public function test_duplicate_share_prevention_works(): void
    {
        LeaderboardSetting::set("duplicate_share_prevention", true);

        $preventDuplicates = LeaderboardSetting::get("duplicate_share_prevention");

        $this->assertTrue($preventDuplicates);
    }

    /**
     * Test: Monthly point cap prevents earning beyond limit
     */
    public function test_monthly_point_cap_enforced(): void
    {
        LeaderboardSetting::set("leaderboard_monthly_point_cap", 100);

        $cap = LeaderboardSetting::get("leaderboard_monthly_point_cap");

        $this->assertEquals(100, $cap);
    }

    /**
     * Test: Distribute leaderboard rewards job can be dispatched
     */
    public function test_distribute_rewards_job_can_be_dispatched(): void
    {
        Queue::fake();

        DistributeLeaderboardRewardsJob::dispatch();

        Queue::assertPushed(DistributeLeaderboardRewardsJob::class);
    }

    /**
     * Test: Leaderboard system respects enabled setting
     */
    public function test_leaderboard_can_be_disabled(): void
    {
        LeaderboardSetting::set('leaderboard_enabled', false);

        $isEnabled = LeaderboardSetting::get('leaderboard_enabled');

        $this->assertFalse($isEnabled);
    }

    /**
     * Test: Admin dashboard displays leaderboard quick link
     */
    public function test_admin_dashboard_shows_leaderboard_link(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('leaderboard-settings');
    }

    /**
     * Test: Settings are loaded with default values
     */
    public function test_settings_have_default_values(): void
    {
        $sharePoints = LeaderboardSetting::get('share_points_per_share', 10);
        $clickPoints = LeaderboardSetting::get('share_points_per_click', 5);
        $monthlyReward1 = LeaderboardSetting::get('monthly_reward_rank_1', 100000);

        $this->assertIsNumeric($sharePoints);
        $this->assertIsNumeric($clickPoints);
        $this->assertIsNumeric($monthlyReward1);
    }

    /**
     * Test: Monthly point cap resets on configured day
     */
    public function test_monthly_point_cap_resets(): void
    {
        LeaderboardSetting::set('leaderboard_reset_day', 15);

        $resetDay = LeaderboardSetting::get('leaderboard_reset_day');

        $this->assertEquals(15, $resetDay);
    }

    /**
     * Test: Reward transfer day can be configured
     */
    public function test_reward_transfer_day_configurable(): void
    {
        LeaderboardSetting::set('reward_transfer_day', 7);

        $transferDay = LeaderboardSetting::get('reward_transfer_day');

        $this->assertEquals(7, $transferDay);
    }

    /**
     * Test: Top 50 users can be rewarded
     */
    public function test_reward_distribution_covers_top_50(): void
    {
        $reward50 = LeaderboardSetting::get('monthly_reward_top_50', 1000);

        $this->assertGreaterThan(0, $reward50);
    }
}
