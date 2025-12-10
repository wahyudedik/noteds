<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'seller']);
    Role::firstOrCreate(['name' => 'buyer']);
});

test('buyer can access buyer dashboard', function () {
    $buyer = User::factory()->create([
        'ktp_path' => 'path/to/ktp.jpg',
        'selfie_path' => 'path/to/selfie.jpg',
        'username' => 'buyer_user',
    ]);
    $buyer->assignRole('buyer');

    $response = $this->actingAs($buyer)->get('/dashboard');

    $response->assertSuccessful();
    $response->assertViewIs('dashboard.buyer');
});

test('seller can access seller dashboard', function () {
    $seller = User::factory()->create([
        'ktp_path' => 'path/to/ktp.jpg',
        'selfie_path' => 'path/to/selfie.jpg',
        'username' => 'seller_user',
    ]);
    $seller->assignRole('seller');

    $response = $this->actingAs($seller)->get('/dashboard');

    $response->assertSuccessful();
    $response->assertViewIs('dashboard.seller');
});

test('buyer dashboard has required metrics', function () {
    $buyer = User::factory()->create([
        'ktp_path' => 'path/to/ktp.jpg',
        'selfie_path' => 'path/to/selfie.jpg',
        'username' => 'buyer_user',
    ]);
    $buyer->assignRole('buyer');

    $response = $this->actingAs($buyer)->get('/dashboard');

    $response->assertViewHas('metrics', function ($metrics) {
        return isset($metrics['total_spent']) &&
            isset($metrics['notes_purchased']) &&
            isset($metrics['collections_count']) &&
            isset($metrics['total_ratings']);
    });
});

test('seller dashboard has required metrics', function () {
    $seller = User::factory()->create([
        'ktp_path' => 'path/to/ktp.jpg',
        'selfie_path' => 'path/to/selfie.jpg',
        'username' => 'seller_user',
    ]);
    $seller->assignRole('seller');

    $response = $this->actingAs($seller)->get('/dashboard');

    $response->assertViewHas('metrics', function ($metrics) {
        return isset($metrics['total_revenue']) &&
            isset($metrics['notes_published']) &&
            isset($metrics['total_sales']) &&
            isset($metrics['average_rating']);
    });
});

test('buyer dashboard shows buyer specific data', function () {
    $buyer = User::factory()->create([
        'ktp_path' => 'path/to/ktp.jpg',
        'selfie_path' => 'path/to/selfie.jpg',
        'username' => 'buyer_user',
    ]);
    $buyer->assignRole('buyer');

    $response = $this->actingAs($buyer)->get('/dashboard');

    // Check for buyer-specific variables
    $response->assertViewHas('recentPurchases');
    $response->assertViewHas('referralStats');
    $response->assertViewHas('wishlisted');
});

test('seller dashboard shows seller specific data', function () {
    $seller = User::factory()->create([
        'ktp_path' => 'path/to/ktp.jpg',
        'selfie_path' => 'path/to/selfie.jpg',
        'username' => 'seller_user',
    ]);
    $seller->assignRole('seller');

    $response = $this->actingAs($seller)->get('/dashboard');

    // Check for seller-specific variables
    $response->assertViewHas('bestPerforming');
    $response->assertViewHas('affiliateStats');
    $response->assertViewHas('recentSales');
    $response->assertViewHas('salesTrend');
});

test('admin is redirected from user dashboard', function () {
    $admin = User::factory()->create([
        'ktp_path' => 'path/to/ktp.jpg',
        'selfie_path' => 'path/to/selfie.jpg',
        'username' => 'admin_user',
    ]);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/dashboard');

    $response->assertRedirect(route('admin.dashboard'));
});

test('incomplete profile redirects to profile edit', function () {
    $buyer = User::factory()->create([
        'ktp_path' => null,
        'selfie_path' => null,
        'username' => 'buyer_user',
        'created_at' => now(),
    ]);
    $buyer->assignRole('buyer');

    $response = $this->actingAs($buyer)->get('/dashboard');

    $response->assertRedirect(route('profile.edit'));
});
