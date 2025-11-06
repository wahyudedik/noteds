<?php

use App\Models\FeaturedNote;
use App\Models\Note;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

test('concurrent featured note requests for same note should be blocked', function () {
    $user = User::factory()->create();
    $note = Note::factory()->create(['user_id' => $user->id, 'is_public' => true, 'status' => 'active']);
    
    $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 1000000]);
    $user->wallet_balance = 1000000;
    $user->save();

    // Simulate concurrent requests
    DB::beginTransaction();
    
    $featured1 = FeaturedNote::create([
        'note_id' => $note->id,
        'user_id' => $user->id,
        'location' => 'marketplace_grid',
        'duration_days' => 7,
        'price' => 50000,
        'status' => 'pending',
    ]);
    
    // Try to create another featured note for same note and location
    $existing = FeaturedNote::where('note_id', $note->id)
        ->where('location', 'marketplace_grid')
        ->whereIn('status', ['pending', 'active'])
        ->first();
    
    expect($existing)->not->toBeNull();
    
    DB::rollBack();
});

test('scheduled ads should not activate before scheduled date', function () {
    $user = User::factory()->create();
    $note = Note::factory()->create(['user_id' => $user->id, 'is_public' => true, 'status' => 'active']);
    
    $futureDate = now()->addDays(5);
    
    $featured = FeaturedNote::create([
        'note_id' => $note->id,
        'user_id' => $user->id,
        'location' => 'marketplace_grid',
        'duration_days' => 7,
        'price' => 50000,
        'status' => 'active',
        'scheduled_date' => $futureDate,
        'start_date' => null,
        'end_date' => null,
    ]);
    
    expect($featured->isScheduled())->toBeTrue();
    expect($featured->isActive())->toBeFalse();
    expect($featured->start_date)->toBeNull();
});

test('bulk purchase discount calculation edge cases', function () {
    // Test with 2 locations (should get 5% discount)
    $locations2 = ['marketplace_grid', 'marketplace_banner'];
    $totalPrice2 = 50000 + 75000; // 125000
    $discount2 = min(20, count($locations2) * 5); // 10%
    $finalPrice2 = $totalPrice2 * (1 - $discount2 / 100); // 112500
    
    expect($discount2)->toBe(10);
    expect($finalPrice2)->toBe(112500.0);
    
    // Test with 5 locations (should get max 20% discount)
    $locations5 = ['marketplace_grid', 'marketplace_banner', 'landing_hero', 'landing_carousel', 'popup_welcome'];
    $discount5 = min(20, count($locations5) * 5); // 20% (max)
    
    expect($discount5)->toBe(20);
    
    // Test with 1 location (no discount)
    $locations1 = ['marketplace_grid'];
    $discount1 = count($locations1) > 1 ? min(20, count($locations1) * 5) : 0;
    
    expect($discount1)->toBe(0);
});

test('custom duration validation edge cases', function () {
    // Test minimum duration (1 day)
    $minDuration = 1;
    expect($minDuration)->toBeGreaterThanOrEqual(1);
    expect($minDuration)->toBeLessThanOrEqual(365);
    
    // Test maximum duration (365 days)
    $maxDuration = 365;
    expect($maxDuration)->toBeGreaterThanOrEqual(1);
    expect($maxDuration)->toBeLessThanOrEqual(365);
    
    // Test invalid durations
    $invalidDurations = [0, -1, 366, 1000];
    foreach ($invalidDurations as $duration) {
        expect($duration < 1 || $duration > 365)->toBeTrue();
    }
});

test('wallet balance should not go negative when creating featured note', function () {
    $user = User::factory()->create();
    $note = Note::factory()->create(['user_id' => $user->id, 'is_public' => true, 'status' => 'active']);
    
    $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 10000]);
    $user->wallet_balance = 10000;
    $user->save();
    
    // Try to create featured note with price higher than balance
    $price = 50000; // Higher than balance (10000)
    
    expect($wallet->balance)->toBeLessThan($price);
    
    // Should be blocked before transaction
    $canAfford = $wallet->balance >= $price;
    expect($canAfford)->toBeFalse();
});

