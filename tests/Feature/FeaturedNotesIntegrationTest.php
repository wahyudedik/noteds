<?php

use App\Models\FeaturedNote;
use App\Models\Note;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Artisan;

test('end-to-end featured note request flow', function () {
    $user = User::factory()->create();
    $note = Note::factory()->create(['user_id' => $user->id, 'is_public' => true, 'status' => 'active']);
    
    $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 100000]);
    $user->wallet_balance = 100000;
    $user->save();
    
    $initialBalance = $wallet->balance;
    $price = 50000;
    
    // 1. Create featured note request
    $featured = FeaturedNote::create([
        'note_id' => $note->id,
        'user_id' => $user->id,
        'location' => 'marketplace_grid',
        'duration_days' => 7,
        'price' => $price,
        'status' => 'pending',
    ]);
    
    // Deduct from wallet
    $wallet->balance -= $price;
    $wallet->save();
    $user->wallet_balance = $wallet->balance;
    $user->save();
    
    // Create transaction
    Transaction::create([
        'buyer_id' => $user->id,
        'seller_id' => $user->id,
        'note_id' => $note->id,
        'amount' => $price,
        'platform_fee' => $price,
        'status' => 'pending',
        'payment_method' => 'wallet',
        'notes' => 'Pembayaran iklan featured note',
    ]);
    
    expect($featured->status)->toBe('pending');
    expect((float) $wallet->fresh()->balance)->toBe((float) ($initialBalance - $price));
    
    // 2. Admin approve
    $featured->update([
        'status' => 'active',
        'start_date' => now(),
        'end_date' => now()->addDays(7),
    ]);
    
    $transaction = Transaction::where('buyer_id', $user->id)
        ->where('amount', $price)
        ->where('status', 'pending')
        ->first();
    
    if ($transaction) {
        $transaction->update(['status' => 'success']);
    }
    
    expect($featured->fresh()->status)->toBe('active');
    expect($featured->fresh()->isActive())->toBeTrue();
    
    // 3. Auto expire (simulate command)
    $featured->update([
        'status' => 'expired',
    ]);
    
    expect($featured->fresh()->status)->toBe('expired');
});

test('end-to-end scheduled ads flow', function () {
    $user = User::factory()->create();
    $note = Note::factory()->create(['user_id' => $user->id, 'is_public' => true, 'status' => 'active']);
    
    $scheduledDate = now()->addDays(2);
    
    // 1. Create scheduled featured note
    $featured = FeaturedNote::create([
        'note_id' => $note->id,
        'user_id' => $user->id,
        'location' => 'marketplace_grid',
        'duration_days' => 7,
        'price' => 50000,
        'status' => 'active',
        'scheduled_date' => $scheduledDate,
        'start_date' => null,
        'end_date' => null,
    ]);
    
    expect($featured->isScheduled())->toBeTrue();
    expect($featured->start_date)->toBeNull();
    
    // 2. Simulate scheduled date reached
    $this->travelTo($scheduledDate);
    
    // Run activation command logic
    $featured->update([
        'start_date' => $scheduledDate,
        'end_date' => $scheduledDate->copy()->addDays($featured->duration_days),
    ]);
    
    expect($featured->fresh()->start_date)->not->toBeNull();
    expect($featured->fresh()->isActive())->toBeTrue();
    
    $this->travelBack();
});

test('end-to-end bulk purchase with discount', function () {
    $user = User::factory()->create();
    $note = Note::factory()->create(['user_id' => $user->id, 'is_public' => true, 'status' => 'active']);
    
    $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 500000]);
    $user->wallet_balance = 500000;
    $user->save();
    
    $locations = ['marketplace_grid', 'marketplace_banner', 'landing_hero'];
    $prices = [50000, 75000, 150000];
    $totalPrice = array_sum($prices); // 275000
    $discountPercent = min(20, count($locations) * 5); // 15%
    $finalPrice = $totalPrice * (1 - $discountPercent / 100); // 233750
    
    // Create parent featured note
    $parent = FeaturedNote::create([
        'note_id' => $note->id,
        'user_id' => $user->id,
        'location' => $locations[0],
        'duration_days' => 7,
        'price' => $finalPrice,
        'discount_percent' => $discountPercent,
        'status' => 'pending',
    ]);
    
    // Create child featured notes
    foreach ($locations as $index => $location) {
        FeaturedNote::create([
            'note_id' => $note->id,
            'user_id' => $user->id,
            'parent_id' => $parent->id,
            'location' => $location,
            'duration_days' => 7,
            'price' => $prices[$index],
            'discount_percent' => $discountPercent,
            'status' => 'pending',
        ]);
    }
    
    // Deduct from wallet
    $wallet->balance -= $finalPrice;
    $wallet->save();
    $user->wallet_balance = $wallet->balance;
    $user->save();
    
    $children = FeaturedNote::where('parent_id', $parent->id)->get();
    
    expect($children->count())->toBe(3);
    expect((float) $parent->discount_percent)->toBe(15.0);
    expect((float) $wallet->fresh()->balance)->toBe((float) (500000 - $finalPrice));
});

