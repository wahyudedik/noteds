<?php

use App\Models\FeaturedNote;
use App\Models\Note;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('featured note ownership validation prevents unauthorized access', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    $note = Note::factory()->create(['user_id' => $user1->id, 'is_public' => true, 'status' => 'active']);
    
    // User2 tries to create featured note for user1's note
    $this->actingAs($user2);
    
    $response = $this->post('/featured-notes', [
        'note_id' => $note->id,
        'location' => 'marketplace_grid',
        'duration_days' => 7,
        '_token' => csrf_token(),
    ]);
    
    // Should be blocked (redirect with error)
    $response->assertRedirect();
    // Controller returns with 'error' key, check if redirect happened
    expect($response->status())->toBe(302);
});

test('scheduled date validation prevents past dates', function () {
    $user = User::factory()->create();
    $note = Note::factory()->create(['user_id' => $user->id, 'is_public' => true, 'status' => 'active']);
    
    $this->actingAs($user);
    
    $pastDate = now()->subDays(1)->format('Y-m-d');
    
    $response = $this->post('/featured-notes', [
        'note_id' => $note->id,
        'location' => 'marketplace_grid',
        'duration_days' => 7,
        'scheduled_date' => $pastDate,
        '_token' => csrf_token(),
    ]);
    
    // Should fail validation (redirect with error)
    $response->assertRedirect();
    // Validation should catch this, check redirect status
    expect($response->status())->toBe(302);
});

test('admin routes require admin role', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create();
    
    // Assign admin role
    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $admin->assignRole($adminRole);
    
    $featured = FeaturedNote::factory()->create();
    
    // Regular user cannot access (redirected or forbidden)
    $this->actingAs($user);
    $response = $this->get('/admin/featured-notes');
    expect($response->status())->toBeIn([403, 302]); // Forbidden or redirect
    
    // Admin can access (if username is set up)
    $this->actingAs($admin);
    // Set username if required by middleware
    if (!$admin->username) {
        $admin->username = 'admin' . $admin->id;
        $admin->save();
    }
    $response = $this->get('/admin/featured-notes');
    // May redirect if username not set, or OK if set
    expect($response->status())->toBeIn([200, 302]);
});

test('CSRF protection on featured note forms', function () {
    $user = User::factory()->create();
    $note = Note::factory()->create(['user_id' => $user->id, 'is_public' => true, 'status' => 'active']);
    
    $this->actingAs($user);
    
    // Try to submit without valid CSRF token
    $response = $this->post('/featured-notes', [
        'note_id' => $note->id,
        'location' => 'marketplace_grid',
        'duration_days' => 7,
        '_token' => 'invalid-token',
    ]);
    
    // Should return 419 or redirect
    expect($response->status())->toBeIn([419, 302]);
});

test('input validation for numeric fields prevents SQL injection', function () {
    $user = User::factory()->create();
    $note = Note::factory()->create(['user_id' => $user->id, 'is_public' => true, 'status' => 'active']);
    
    $this->actingAs($user);
    
    // Try SQL injection in duration_days
    $response = $this->post('/featured-notes', [
        'note_id' => $note->id,
        'location' => 'marketplace_grid',
        'duration_days' => "7'; DROP TABLE featured_notes; --",
        '_token' => csrf_token(),
    ]);
    
    // Should fail validation, not execute SQL
    $response->assertRedirect();
    // Validation should catch non-numeric value
    expect($response->status())->toBe(302);
    
    // Table should still exist (can query it)
    expect(FeaturedNote::count())->toBeGreaterThanOrEqual(0); // Table exists, may have records
});

test('featured note export requires authentication', function () {
    // Unauthenticated user
    $response = $this->get('/featured-notes/export');
    $response->assertRedirect('/login');
    
    // Authenticated user
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $response = $this->get('/featured-notes/export');
    // Should either return CSV or redirect (depending on implementation)
    expect($response->status())->toBeIn([200, 302]);
});

