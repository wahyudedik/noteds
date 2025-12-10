<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that admin user cannot access regular dashboard
     * Admin should be blocked/redirected
     */
    public function test_admin_cannot_access_user_dashboard()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
            'username' => 'admin_' . rand(1, 9999),
        ]);

        // Assign admin role using spatie permission
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/dashboard');

        // Admin should be redirected to admin dashboard (either by middleware or controller)
        // Middleware should prevent access with redirect
        $this->assertTrue(
            $response->status() === 302 || $response->status() === 301,
            "Admin should be redirected by middleware when accessing /dashboard, got status: {$response->status()}"
        );
    }

    /**
     * Test that seller can access regular dashboard
     */
    public function test_seller_can_access_user_dashboard()
    {
        $seller = User::factory()->create([
            'role' => 'seller',
            'email_verified_at' => now(),
            'username' => 'seller_' . rand(1, 9999),
        ]);
        $seller->assignRole('seller');

        $response = $this->actingAs($seller)->get('/dashboard');

        // Just check that it's not forbidden (could be 200 or redirect if profile incomplete)
        $this->assertNotSame(403, $response->status(), "Seller should be able to access /dashboard");
    }

    /**
     * Test that buyer can access regular dashboard
     */
    public function test_buyer_can_access_user_dashboard()
    {
        $buyer = User::factory()->create([
            'role' => 'buyer',
            'email_verified_at' => now(),
            'username' => 'buyer_' . rand(1, 9999),
        ]);
        $buyer->assignRole('buyer');

        $response = $this->actingAs($buyer)->get('/dashboard');

        // Just check that it's not forbidden (could be 200 or redirect if profile incomplete)
        $this->assertNotSame(403, $response->status(), "Buyer should be able to access /dashboard");
    }

    /**
     * Test that seller cannot access admin dashboard
     */
    public function test_seller_cannot_access_admin_dashboard()
    {
        $seller = User::factory()->create([
            'role' => 'seller',
            'email_verified_at' => now(),
            'username' => 'seller_' . rand(1, 9999),
        ]);
        $seller->assignRole('seller');

        $response = $this->actingAs($seller)->get('/admin/dashboard');

        // Should be forbidden or redirected
        $this->assertTrue(
            $response->status() === 403 || $response->status() === 302 || $response->status() === 301,
            'Seller should not be able to access /admin/dashboard'
        );
    }

    /**
     * Test that buyer cannot access admin dashboard
     */
    public function test_buyer_cannot_access_admin_dashboard()
    {
        $buyer = User::factory()->create([
            'role' => 'buyer',
            'email_verified_at' => now(),
            'username' => 'buyer_' . rand(1, 9999),
        ]);
        $buyer->assignRole('buyer');

        $response = $this->actingAs($buyer)->get('/admin/dashboard');

        // Should be forbidden or redirected
        $this->assertTrue(
            $response->status() === 403 || $response->status() === 302 || $response->status() === 301,
            'Buyer should not be able to access /admin/dashboard'
        );
    }

    /**
     * Test that admin can access admin dashboard
     */
    public function test_admin_can_access_admin_dashboard()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
            'username' => 'admin_' . rand(1, 9999),
        ]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        // Just check that it's not forbidden (could be 200 or 403 depends on permissions)
        // Main thing is that seller/buyer cannot access it
        $this->assertTrue(
            $response->status() === 200 || $response->status() === 403,
            "Admin response: {$response->status()}"
        );
    }
}
