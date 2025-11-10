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

test('buyer can set resale price via resale form', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $buyer = User::factory()->create(['role' => 'buyer']);
    Wallet::factory()->create(['user_id' => $buyer->id, 'balance' => 200000]);
    $buyer->wallet_balance = 200000;
    $buyer->save();

    $note = Note::factory()->create([
        'user_id' => $buyer->id, // Buyer owns it
        'sale_mode' => 'scarcity',
        'price' => 100000,
        'is_public' => true,
        'status' => 'active',
    ]);

    PurchasedNote::create([
        'user_id' => $buyer->id,
        'note_id' => $note->id,
        'purchase_price' => 100000,
        'purchased_at' => now(),
    ]);

    Transaction::factory()->create([
        'buyer_id' => $buyer->id,
        'note_id' => $note->id,
        'status' => 'success',
    ]);

    $response = $this->actingAs($buyer)->get(route('notes.resale.form', $note));
    $response->assertOk();

    $response = $this->actingAs($buyer)->post(route('notes.resale', $note), [
        'resale_price' => 120000,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $note->refresh();
    expect($note->price)->toBe(120000.0);
});

test('buyer cannot resale standard mode note', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $buyer = User::factory()->create(['role' => 'buyer']);
    Wallet::factory()->create(['user_id' => $buyer->id, 'balance' => 200000]);
    $buyer->wallet_balance = 200000;
    $buyer->save();

    $note = Note::factory()->create([
        'user_id' => $buyer->id,
        'sale_mode' => 'standard', // Standard mode
        'price' => 100000,
        'is_public' => true,
        'status' => 'active',
    ]);

    $response = $this->actingAs($buyer)->get(route('notes.resale.form', $note));
    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('resale price validation works correctly', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $buyer = User::factory()->create(['role' => 'buyer']);
    Wallet::factory()->create(['user_id' => $buyer->id, 'balance' => 200000]);
    $buyer->wallet_balance = 200000;
    $buyer->save();

    $note = Note::factory()->create([
        'user_id' => $buyer->id,
        'sale_mode' => 'scarcity',
        'price' => 100000,
        'is_public' => true,
        'status' => 'active',
    ]);

    PurchasedNote::create([
        'user_id' => $buyer->id,
        'note_id' => $note->id,
        'purchase_price' => 100000,
        'purchased_at' => now(),
    ]);

    Transaction::factory()->create([
        'buyer_id' => $buyer->id,
        'note_id' => $note->id,
        'status' => 'success',
    ]);

    // Test negative price
    $response = $this->actingAs($buyer)->post(route('notes.resale', $note), [
        'resale_price' => -1000,
    ]);
    $response->assertSessionHasErrors('resale_price');

    // Test zero price
    $response = $this->actingAs($buyer)->post(route('notes.resale', $note), [
        'resale_price' => 0,
    ]);
    $response->assertSessionHasErrors('resale_price');
});

test('resale transaction records resale_price and sold_at', function () {
    $creator = User::factory()->create(['role' => 'seller']);
    $buyer1 = User::factory()->create(['role' => 'buyer']);
    $buyer2 = User::factory()->create(['role' => 'buyer']);
    
    Wallet::factory()->create(['user_id' => $buyer1->id, 'balance' => 200000]);
    Wallet::factory()->create(['user_id' => $buyer2->id, 'balance' => 200000]);
    $buyer1->wallet_balance = 200000;
    $buyer1->save();
    $buyer2->wallet_balance = 200000;
    $buyer2->save();

    $note = Note::factory()->create([
        'user_id' => $creator->id,
        'original_creator_id' => $creator->id,
        'sale_mode' => 'scarcity',
        'price' => 100000,
        'is_public' => true,
        'status' => 'active',
    ]);

    // Buyer 1 purchases
    $this->actingAs($buyer1)->post(route('marketplace.purchase', $note));
    
    // Buyer 1 sets resale price
    $note->user_id = $buyer1->id;
    $note->price = 120000;
    $note->save();
    
    // Buyer 2 purchases from buyer 1 (resale)
    $this->actingAs($buyer2)->post(route('marketplace.purchase', $note));

    $resaleTransaction = Transaction::where('buyer_id', $buyer2->id)
        ->where('note_id', $note->id)
        ->where('seller_id', $buyer1->id)
        ->first();

    expect((float) $resaleTransaction->resale_price)->toBe(120000.0);
    expect($resaleTransaction->sold_at)->not->toBeNull();
});

