<?php

use App\Models\Note;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
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

test('multiple buyers can purchase same note in standard mode', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $buyer1 = User::factory()->create(['role' => 'buyer']);
    $buyer2 = User::factory()->create(['role' => 'buyer']);
    
    Wallet::factory()->create(['user_id' => $buyer1->id, 'balance' => 200000]);
    Wallet::factory()->create(['user_id' => $buyer2->id, 'balance' => 200000]);
    $buyer1->wallet_balance = 200000;
    $buyer1->save();
    $buyer2->wallet_balance = 200000;
    $buyer2->save();

    $note = Note::factory()->create([
        'user_id' => $seller->id,
        'sale_mode' => 'standard',
        'price' => 100000,
        'is_public' => true,
        'status' => 'active',
    ]);

    // Buyer 1 purchases
    $this->actingAs($buyer1)->post(route('marketplace.purchase', $note));
    
    // Buyer 2 purchases same note
    $response = $this->actingAs($buyer2)->post(route('marketplace.purchase', $note));

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    // Both transactions should exist
    expect(Transaction::where('note_id', $note->id)->where('status', 'success')->count())->toBe(2);
    
    // Ownership should remain with seller
    $note->refresh();
    expect($note->user_id)->toBe($seller->id);
});

test('buyer cannot purchase from same seller twice in standard mode', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $buyer = User::factory()->create(['role' => 'buyer']);
    Wallet::factory()->create(['user_id' => $buyer->id, 'balance' => 200000]);
    $buyer->wallet_balance = 200000;
    $buyer->save();

    $note = Note::factory()->create([
        'user_id' => $seller->id,
        'sale_mode' => 'standard',
        'price' => 100000,
        'is_public' => true,
        'status' => 'active',
    ]);

    // First purchase
    $this->actingAs($buyer)->post(route('marketplace.purchase', $note));

    // Try to purchase again from same seller
    $buyer->wallet_balance = 200000;
    $buyer->save();
    $response = $this->actingAs($buyer)->post(route('marketplace.purchase', $note));

    $response->assertSessionHas('error');
});

test('no commission in standard mode', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $buyer = User::factory()->create(['role' => 'buyer']);
    Wallet::factory()->create(['user_id' => $buyer->id, 'balance' => 200000]);
    $buyer->wallet_balance = 200000;
    $buyer->save();

    $note = Note::factory()->create([
        'user_id' => $seller->id,
        'sale_mode' => 'standard',
        'price' => 100000,
        'is_public' => true,
        'status' => 'active',
    ]);

    $this->actingAs($buyer)->post(route('marketplace.purchase', $note));

    $transaction = Transaction::where('buyer_id', $buyer->id)
        ->where('note_id', $note->id)
        ->first();

    expect($transaction->platform_fee)->toBe(0.0);
    expect($transaction->creator_commission)->toBe(0.0);
});

test('seller gets full amount minus tax in standard mode', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $buyer = User::factory()->create(['role' => 'buyer']);
    Wallet::factory()->create(['user_id' => $buyer->id, 'balance' => 200000]);
    Wallet::factory()->create(['user_id' => $seller->id, 'balance' => 0]);
    $buyer->wallet_balance = 200000;
    $buyer->save();
    $seller->wallet_balance = 0;
    $seller->save();

    $note = Note::factory()->create([
        'user_id' => $seller->id,
        'sale_mode' => 'standard',
        'price' => 100000,
        'is_public' => true,
        'status' => 'active',
    ]);

    $sellerBalanceBefore = $seller->wallet_balance;

    $this->actingAs($buyer)->post(route('marketplace.purchase', $note));

    $seller->refresh();
    // Seller should get full amount (minus tax if any)
    expect($seller->wallet_balance)->toBeGreaterThan($sellerBalanceBefore);
});

test('ownership stays with seller in standard mode', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $buyer = User::factory()->create(['role' => 'buyer']);
    Wallet::factory()->create(['user_id' => $buyer->id, 'balance' => 200000]);
    $buyer->wallet_balance = 200000;
    $buyer->save();

    $note = Note::factory()->create([
        'user_id' => $seller->id,
        'sale_mode' => 'standard',
        'price' => 100000,
        'is_public' => true,
        'status' => 'active',
    ]);

    $this->actingAs($buyer)->post(route('marketplace.purchase', $note));

    $note->refresh();
    expect($note->user_id)->toBe($seller->id);
});

test('no grace period in standard mode', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $buyer = User::factory()->create(['role' => 'buyer']);
    Wallet::factory()->create(['user_id' => $buyer->id, 'balance' => 200000]);
    $buyer->wallet_balance = 200000;
    $buyer->save();

    $note = Note::factory()->create([
        'user_id' => $seller->id,
        'sale_mode' => 'standard',
        'price' => 100000,
        'is_public' => true,
        'status' => 'active',
    ]);

    $this->actingAs($buyer)->post(route('marketplace.purchase', $note));

    $transaction = Transaction::where('buyer_id', $buyer->id)
        ->where('note_id', $note->id)
        ->first();

    expect($transaction->grace_period_ends_at)->toBeNull();
});

