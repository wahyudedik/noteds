<?php

use App\Models\Note;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\PurchasedNote;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    
    $admin = User::factory()->create(['role' => 'admin']);
    Wallet::factory()->create(['user_id' => $admin->id, 'balance' => 0]);
    
    Setting::firstOrCreate(['key' => 'platform_fee_percent'], ['value' => '20']);
    Setting::firstOrCreate(['key' => 'creator_commission_percent'], ['value' => '10']);
});

test('buyer can repurchase within grace period at original price', function () {
    $creator = User::factory()->create(['role' => 'seller']);
    $buyer = User::factory()->create(['role' => 'buyer']);
    $newOwner = User::factory()->create(['role' => 'buyer']);
    
    Wallet::factory()->create(['user_id' => $buyer->id, 'balance' => 300000]);
    Wallet::factory()->create(['user_id' => $newOwner->id, 'balance' => 200000]);
    $buyer->wallet_balance = 300000;
    $buyer->save();
    $newOwner->wallet_balance = 200000;
    $newOwner->save();

    $note = Note::factory()->create([
        'user_id' => $creator->id,
        'original_creator_id' => $creator->id,
        'sale_mode' => 'scarcity',
        'price' => 100000,
        'is_public' => true,
        'status' => 'active',
        'grace_period_days' => 30,
        'relist_price_multiplier' => 1.5,
    ]);

    // First purchase
    $this->actingAs($buyer)->post(route('marketplace.purchase', $note));
    
    $firstTransaction = Transaction::where('buyer_id', $buyer->id)
        ->where('note_id', $note->id)
        ->first();
    
    // Buyer sells to new owner
    $note->user_id = $newOwner->id;
    $note->save();
    
    // Buyer tries to repurchase within grace period
    $buyer->wallet_balance = 300000;
    $buyer->save();
    $response = $this->actingAs($buyer)->post(route('marketplace.purchase', $note));

    $response->assertRedirect();
    
    $repurchaseTransaction = Transaction::where('buyer_id', $buyer->id)
        ->where('note_id', $note->id)
        ->where('id', '!=', $firstTransaction->id)
        ->first();

    expect($repurchaseTransaction)->not->toBeNull();
    // Should be original price (100000), not premium
    expect((float) $repurchaseTransaction->amount)->toBe(100000.0);
});

test('buyer can repurchase after grace period at premium price', function () {
    $creator = User::factory()->create(['role' => 'seller']);
    $buyer = User::factory()->create(['role' => 'buyer']);
    $newOwner = User::factory()->create(['role' => 'buyer']);
    
    Wallet::factory()->create(['user_id' => $buyer->id, 'balance' => 300000]);
    Wallet::factory()->create(['user_id' => $newOwner->id, 'balance' => 200000]);
    $buyer->wallet_balance = 300000;
    $buyer->save();
    $newOwner->wallet_balance = 200000;
    $newOwner->save();

    $note = Note::factory()->create([
        'user_id' => $creator->id,
        'original_creator_id' => $creator->id,
        'sale_mode' => 'scarcity',
        'price' => 100000,
        'is_public' => true,
        'status' => 'active',
        'grace_period_days' => 30,
        'relist_price_multiplier' => 1.5,
    ]);

    // First purchase
    $this->actingAs($buyer)->post(route('marketplace.purchase', $note));
    
    $firstTransaction = Transaction::where('buyer_id', $buyer->id)
        ->where('note_id', $note->id)
        ->first();
    
    // Expire grace period
    $firstTransaction->grace_period_ends_at = now()->subDays(10);
    $firstTransaction->save();
    
    // Buyer sells to new owner
    $note->user_id = $newOwner->id;
    $note->save();
    
    // Buyer tries to repurchase after grace period
    $buyer->wallet_balance = 300000;
    $buyer->save();
    $response = $this->actingAs($buyer)->post(route('marketplace.purchase', $note));

    $response->assertRedirect();
    
    $repurchaseTransaction = Transaction::where('buyer_id', $buyer->id)
        ->where('note_id', $note->id)
        ->where('id', '!=', $firstTransaction->id)
        ->first();

    expect($repurchaseTransaction)->not->toBeNull();
    // Should be premium price (100000 * 1.5 = 150000)
    expect((float) $repurchaseTransaction->amount)->toBe(150000.0);
});

test('buyer cannot repurchase if they still own the note', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $buyer = User::factory()->create(['role' => 'buyer']);
    Wallet::factory()->create(['user_id' => $buyer->id, 'balance' => 200000]);
    $buyer->wallet_balance = 200000;
    $buyer->save();

    $note = Note::factory()->create([
        'user_id' => $buyer->id, // Buyer already owns it
        'sale_mode' => 'scarcity',
        'price' => 100000,
        'is_public' => true,
        'status' => 'active',
    ]);

    Transaction::factory()->create([
        'buyer_id' => $buyer->id,
        'note_id' => $note->id,
        'status' => 'success',
    ]);

    $response = $this->actingAs($buyer)->post(route('marketplace.purchase', $note));
    $response->assertSessionHas('error');
});

