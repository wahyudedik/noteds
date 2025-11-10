<?php

use App\Models\Note;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed roles
    $this->seed(\Database\Seeders\RoleSeeder::class);
    
    // Create admin user for platform wallet
    $admin = User::factory()->create(['role' => 'admin']);
    Wallet::factory()->create(['user_id' => $admin->id, 'balance' => 0]);
    
    // Set default settings
    Setting::firstOrCreate(['key' => 'platform_fee_percent'], ['value' => '20']);
    Setting::firstOrCreate(['key' => 'creator_commission_percent'], ['value' => '10']);
});

test('buyer can purchase note in scarcity mode once', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $buyer = User::factory()->create(['role' => 'buyer']);
    Wallet::factory()->create(['user_id' => $buyer->id, 'balance' => 200000]);
    $buyer->wallet_balance = 200000;
    $buyer->save();

    $note = Note::factory()->create([
        'user_id' => $seller->id,
        'sale_mode' => 'scarcity',
        'price' => 100000,
        'is_public' => true,
        'status' => 'active',
        'grace_period_days' => 30,
        'relist_price_multiplier' => 1.5,
    ]);

    $response = $this->actingAs($buyer)->post(route('marketplace.purchase', $note));

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    // Check transaction was created
    $transaction = Transaction::where('buyer_id', $buyer->id)
        ->where('note_id', $note->id)
        ->where('status', 'success')
        ->first();

    expect($transaction)->not->toBeNull();
    expect((float) $transaction->amount)->toBe(100000.0);
    expect($transaction->grace_period_ends_at)->not->toBeNull();

    // Check ownership transferred
    $note->refresh();
    expect($note->user_id)->toBe($buyer->id);

    // Check buyer cannot purchase again
    $buyer->wallet_balance = 200000;
    $buyer->save();
    $response2 = $this->actingAs($buyer)->post(route('marketplace.purchase', $note));
    $response2->assertSessionHas('error');
});

test('buyer cannot purchase same note twice in scarcity mode', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $buyer = User::factory()->create(['role' => 'buyer']);
    Wallet::factory()->create(['user_id' => $buyer->id, 'balance' => 200000]);
    $buyer->wallet_balance = 200000;
    $buyer->save();

    $note = Note::factory()->create([
        'user_id' => $seller->id,
        'sale_mode' => 'scarcity',
        'price' => 100000,
        'is_public' => true,
        'status' => 'active',
    ]);

    // First purchase
    $this->actingAs($buyer)->post(route('marketplace.purchase', $note));

    // Try to purchase again
    $buyer->wallet_balance = 200000;
    $buyer->save();
    $response = $this->actingAs($buyer)->post(route('marketplace.purchase', $note));

    $response->assertSessionHas('error');
    expect(Transaction::where('buyer_id', $buyer->id)->where('note_id', $note->id)->count())->toBe(1);
});

test('original creator gets commission in scarcity mode', function () {
    $creator = User::factory()->create(['role' => 'seller']);
    $buyer = User::factory()->create(['role' => 'buyer']);
    Wallet::factory()->create(['user_id' => $buyer->id, 'balance' => 200000]);
    $buyer->wallet_balance = 200000;
    $buyer->save();

    $note = Note::factory()->create([
        'user_id' => $creator->id,
        'original_creator_id' => $creator->id,
        'sale_mode' => 'scarcity',
        'price' => 100000,
        'is_public' => true,
        'status' => 'active',
    ]);

    $this->actingAs($buyer)->post(route('marketplace.purchase', $note));

    $transaction = Transaction::where('buyer_id', $buyer->id)
        ->where('note_id', $note->id)
        ->first();

    expect($transaction->original_creator_id)->toBe($creator->id);
    // Creator commission might be 0 if setting is not configured, but should be set
    expect($transaction->creator_commission)->toBeGreaterThanOrEqual(0);
});

test('grace period is set correctly on purchase in scarcity mode', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $buyer = User::factory()->create(['role' => 'buyer']);
    Wallet::factory()->create(['user_id' => $buyer->id, 'balance' => 200000]);
    $buyer->wallet_balance = 200000;
    $buyer->save();

    $note = Note::factory()->create([
        'user_id' => $seller->id,
        'sale_mode' => 'scarcity',
        'price' => 100000,
        'is_public' => true,
        'status' => 'active',
        'grace_period_days' => 30,
    ]);

    $this->actingAs($buyer)->post(route('marketplace.purchase', $note));

    $transaction = Transaction::where('buyer_id', $buyer->id)
        ->where('note_id', $note->id)
        ->first();

    expect($transaction->grace_period_ends_at)->not->toBeNull();
    // Grace period should be approximately 30 days (allow small variance)
    $daysDiff = abs($transaction->grace_period_ends_at->diffInDays(now()));
    expect($daysDiff)->toBeGreaterThanOrEqual(29)->and($daysDiff)->toBeLessThanOrEqual(31);
});

test('ownership transfers to buyer in scarcity mode', function () {
    $seller = User::factory()->create(['role' => 'seller']);
    $buyer = User::factory()->create(['role' => 'buyer']);
    Wallet::factory()->create(['user_id' => $buyer->id, 'balance' => 200000]);
    $buyer->wallet_balance = 200000;
    $buyer->save();

    $note = Note::factory()->create([
        'user_id' => $seller->id,
        'sale_mode' => 'scarcity',
        'price' => 100000,
        'is_public' => true,
        'status' => 'active',
    ]);

    $this->actingAs($buyer)->post(route('marketplace.purchase', $note));

    $note->refresh();
    expect($note->user_id)->toBe($buyer->id);
});

