<?php

namespace Tests\Feature;

use App\Models\PointsPricingConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PointsPricingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    /**
     * Test: Admin can view points pricing list
     */
    public function test_admin_can_view_points_pricing_list(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/points-pricing');

        $response->assertStatus(200);
        $response->assertViewIs('admin.points-pricing.index');
    }

    /**
     * Test: Admin can create new pricing configuration
     */
    public function test_admin_can_create_pricing_configuration(): void
    {
        $data = [
            'name' => '10% Discount',
            'type' => 'discount',
            'points_required' => 500,
            'discount_percent' => 10,
            'daily_limit' => 50,
            'user_limit' => 2,
            'description' => 'Test discount offer',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->post('/admin/points-pricing', $data);

        $response->assertRedirect('/admin/points-pricing');
        $this->assertDatabaseHas('points_pricing_config', [
            'name' => '10% Discount',
            'type' => 'discount',
        ]);
    }

    /**
     * Test: Admin can edit pricing configuration
     */
    public function test_admin_can_edit_pricing_configuration(): void
    {
        $config = PointsPricingConfig::factory()->create([
            'name' => 'Original Name',
            'discount_percent' => 5,
        ]);

        $data = [
            'name' => 'Updated Name',
            'type' => 'discount',
            'points_required' => 600,
            'discount_percent' => 15,
            'daily_limit' => 100,
            'user_limit' => 3,
            'description' => 'Updated description',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->put("/admin/points-pricing/{$config->id}", $data);

        $response->assertRedirect('/admin/points-pricing');
        $this->assertDatabaseHas('points_pricing_config', [
            'id' => $config->id,
            'name' => 'Updated Name',
            'discount_percent' => 15,
        ]);
    }

    /**
     * Test: Admin can delete pricing configuration
     */
    public function test_admin_can_delete_pricing_configuration(): void
    {
        $config = PointsPricingConfig::factory()->create();

        $response = $this->actingAs($this->admin)->delete("/admin/points-pricing/{$config->id}");

        $response->assertRedirect('/admin/points-pricing');
        $this->assertDatabaseMissing('points_pricing_config', [
            'id' => $config->id,
        ]);
    }

    /**
     * Test: Admin can view redemption monitoring dashboard
     */
    public function test_admin_can_view_monitoring_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/points-monitoring');

        $response->assertStatus(200);
        $response->assertViewIs('admin.points-pricing.monitoring');
    }

    /**
     * Test: Admin can export redemption report
     */
    public function test_admin_can_export_redemption_report(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/points-redemption/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');
    }

    /**
     * Test: Daily limit validation
     */
    public function test_daily_limit_validation(): void
    {
        $config = PointsPricingConfig::factory()->create([
            'daily_limit' => 2,
            'is_active' => true,
        ]);

        // Simulate two redemptions by checking the limit method
        $this->assertFalse($config->isDailyLimitReached());
    }

    /**
     * Test: User limit validation
     */
    public function test_user_limit_validation(): void
    {
        $user = User::factory()->create();

        $config = PointsPricingConfig::factory()->create([
            'user_limit' => 1,
            'is_active' => true,
        ]);

        // One redemption for this user should be OK
        $this->assertFalse($config->isUserLimitReached($user->id));
    }

    /**
     * Test: Active pricing options filter
     */
    public function test_get_active_pricing_options(): void
    {
        // Create active config
        $active = PointsPricingConfig::factory()->create([
            'is_active' => true,
        ]);

        // Create inactive config
        PointsPricingConfig::factory()->create([
            'is_active' => false,
        ]);

        // Create expired config
        PointsPricingConfig::factory()->create([
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $options = PointsPricingConfig::getActiveOptions();

        $this->assertEquals(1, $options->count());
        $this->assertTrue($options->first()->id === $active->id);
    }

    /**
     * Test: Filter by type
     */
    public function test_filter_by_type(): void
    {
        // Create discount configs
        PointsPricingConfig::factory()->count(3)->create(['type' => 'discount']);

        // Create premium feature configs
        PointsPricingConfig::factory()->count(2)->create(['type' => 'premium_feature']);

        $discounts = PointsPricingConfig::getActiveByType('discount');
        $premiums = PointsPricingConfig::getActiveByType('premium_feature');

        $this->assertEquals(3, $discounts->count());
        $this->assertEquals(2, $premiums->count());
    }

    /**
     * Test: Non-admin cannot access pricing controls
     */
    public function test_non_admin_cannot_access_pricing_controls(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/points-pricing');

        // Should be redirected or forbidden
        $this->assertTrue(
            $response->status() === 403 || $response->status() === 302
        );
    }

    /**
     * Test: Unauthenticated user cannot access pricing controls
     */
    public function test_unauthenticated_user_cannot_access_pricing(): void
    {
        $response = $this->get('/admin/points-pricing');

        $response->assertRedirect('/login');
    }

    /**
     * Test: Form validation - missing required fields
     */
    public function test_form_validation_missing_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/points-pricing', [
            // Missing required fields
        ]);

        $response->assertSessionHasErrors(['name', 'type', 'points_required']);
    }

    /**
     * Test: Form validation - invalid type
     */
    public function test_form_validation_invalid_type(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/points-pricing', [
            'name' => 'Test',
            'type' => 'invalid_type',
            'points_required' => 100,
        ]);

        $response->assertSessionHasErrors(['type']);
    }

    /**
     * Test: Display name formatting
     */
    public function test_display_name_formatting(): void
    {
        $config = PointsPricingConfig::factory()->create([
            'name' => 'Premium Access',
            'type' => 'premium_feature',
            'premium_days' => 30,
        ]);

        $displayName = $config->display_name;

        $this->assertStringContainsString('Premium Access', $displayName);
        $this->assertStringContainsString('30', $displayName);
    }

    /**
     * Test: Expiration date validation
     */
    public function test_expiration_date_validation(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/points-pricing', [
            'name' => 'Expired Offer',
            'type' => 'discount',
            'points_required' => 100,
            'discount_percent' => 5,
            'expires_at' => now()->subDay(),
        ]);

        $response->assertSessionHasErrors(['expires_at']);
    }

    /**
     * Test: Pricing config with premium feature type
     */
    public function test_premium_feature_configuration(): void
    {
        $data = [
            'name' => '30 Days Premium',
            'type' => 'premium_feature',
            'points_required' => 1000,
            'premium_days' => 30,
            'daily_limit' => 10,
            'user_limit' => 1,
            'description' => 'Grant 30 days of premium access',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->post('/admin/points-pricing', $data);

        $response->assertRedirect('/admin/points-pricing');
        $this->assertDatabaseHas('points_pricing_config', [
            'name' => '30 Days Premium',
            'type' => 'premium_feature',
            'premium_days' => 30,
        ]);
    }

    /**
     * Test: Pricing config with fixed discount amount
     */
    public function test_fixed_discount_configuration(): void
    {
        $data = [
            'name' => 'Rp50,000 Off',
            'type' => 'discount',
            'points_required' => 300,
            'discount_amount' => 50000,
            'daily_limit' => 20,
            'user_limit' => 2,
            'description' => 'Rp50,000 discount on purchases',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->post('/admin/points-pricing', $data);

        $response->assertRedirect('/admin/points-pricing');
        $this->assertDatabaseHas('points_pricing_config', [
            'discount_amount' => 50000,
            'discount_percent' => null,
        ]);
    }
}
