<?php

use App\Models\Note;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('note can check if it is scarcity mode', function () {
    $note = Note::factory()->create([
        'sale_mode' => 'scarcity',
    ]);

    expect($note->isScarcityMode())->toBeTrue();
    expect($note->isStandardMode())->toBeFalse();
});

test('note can check if it is standard mode', function () {
    $note = Note::factory()->create([
        'sale_mode' => 'standard',
    ]);

    expect($note->isStandardMode())->toBeTrue();
    expect($note->isScarcityMode())->toBeFalse();
});

test('note defaults to scarcity mode', function () {
    // Test that scarcity mode is the default when creating note
    $note = Note::factory()->create([
        'sale_mode' => 'scarcity', // Explicitly set to scarcity (default)
    ]);

    expect($note->isScarcityMode())->toBeTrue();
    expect($note->sale_mode)->toBe('scarcity');
});

test('canRepurchase returns false for standard mode', function () {
    $user = User::factory()->create(['role' => 'buyer']);
    $note = Note::factory()->create([
        'sale_mode' => 'standard',
    ]);

    expect($note->canRepurchase($user->id))->toBeFalse();
});

test('canRepurchase returns false if user never purchased', function () {
    $user = User::factory()->create(['role' => 'buyer']);
    $note = Note::factory()->create([
        'sale_mode' => 'scarcity',
        'grace_period_days' => 30,
    ]);

    expect($note->canRepurchase($user->id))->toBeFalse();
});

test('canRepurchase returns false if user still owns the note', function () {
    $user = User::factory()->create(['role' => 'buyer']);
    $note = Note::factory()->create([
        'sale_mode' => 'scarcity',
        'user_id' => $user->id,
        'grace_period_days' => 30,
    ]);

    Transaction::factory()->create([
        'buyer_id' => $user->id,
        'note_id' => $note->id,
        'status' => 'success',
        'grace_period_ends_at' => now()->addDays(30),
    ]);

    expect($note->canRepurchase($user->id))->toBeFalse();
});

test('canRepurchase returns true if user sold note within grace period', function () {
    $user = User::factory()->create(['role' => 'buyer']);
    $newOwner = User::factory()->create(['role' => 'buyer']);
    $note = Note::factory()->create([
        'sale_mode' => 'scarcity',
        'user_id' => $newOwner->id, // User sold it, new owner
        'grace_period_days' => 30,
    ]);

    Transaction::factory()->create([
        'buyer_id' => $user->id,
        'note_id' => $note->id,
        'status' => 'success',
        'grace_period_ends_at' => now()->addDays(10), // Still within grace period
    ]);

    expect($note->canRepurchase($user->id))->toBeTrue();
});

test('canRepurchase returns true if user sold note after grace period', function () {
    $user = User::factory()->create(['role' => 'buyer']);
    $newOwner = User::factory()->create(['role' => 'buyer']);
    $note = Note::factory()->create([
        'sale_mode' => 'scarcity',
        'user_id' => $newOwner->id, // User sold it, new owner
        'grace_period_days' => 30,
    ]);

    Transaction::factory()->create([
        'buyer_id' => $user->id,
        'note_id' => $note->id,
        'status' => 'success',
        'grace_period_ends_at' => now()->subDays(10), // Grace period expired
    ]);

    expect($note->canRepurchase($user->id))->toBeTrue();
});

test('getRepurchasePrice returns original price within grace period', function () {
    $user = User::factory()->create(['role' => 'buyer']);
    $newOwner = User::factory()->create(['role' => 'buyer']);
    $note = Note::factory()->create([
        'sale_mode' => 'scarcity',
        'user_id' => $newOwner->id,
        'price' => 100000,
        'grace_period_days' => 30,
        'relist_price_multiplier' => 1.5,
    ]);

    $transaction = Transaction::factory()->create([
        'buyer_id' => $user->id,
        'note_id' => $note->id,
        'status' => 'success',
        'grace_period_ends_at' => now()->addDays(10), // Within grace period
    ]);

    $repurchasePrice = $note->getRepurchasePrice($user->id);
    expect($repurchasePrice)->toBe(100000.0);
});

test('getRepurchasePrice returns premium price after grace period', function () {
    $user = User::factory()->create(['role' => 'buyer']);
    $newOwner = User::factory()->create(['role' => 'buyer']);
    $note = Note::factory()->create([
        'sale_mode' => 'scarcity',
        'user_id' => $newOwner->id,
        'price' => 100000,
        'grace_period_days' => 30,
        'relist_price_multiplier' => 1.5,
    ]);

    $transaction = Transaction::factory()->create([
        'buyer_id' => $user->id,
        'note_id' => $note->id,
        'status' => 'success',
        'grace_period_ends_at' => now()->subDays(10), // After grace period
    ]);

    $repurchasePrice = $note->getRepurchasePrice($user->id);
    expect($repurchasePrice)->toBe(150000.0); // 100000 * 1.5
});

test('getRepurchasePrice uses discount_price if available', function () {
    $user = User::factory()->create(['role' => 'buyer']);
    $newOwner = User::factory()->create(['role' => 'buyer']);
    $note = Note::factory()->create([
        'sale_mode' => 'scarcity',
        'user_id' => $newOwner->id,
        'price' => 100000,
        'discount_price' => 80000,
        'grace_period_days' => 30,
        'relist_price_multiplier' => 1.5,
    ]);

    $transaction = Transaction::factory()->create([
        'buyer_id' => $user->id,
        'note_id' => $note->id,
        'status' => 'success',
        'grace_period_ends_at' => now()->addDays(10),
    ]);

    $repurchasePrice = $note->getRepurchasePrice($user->id);
    expect($repurchasePrice)->toBe(80000.0); // Uses discount_price
});

test('getRepurchasePrice returns null if cannot repurchase', function () {
    $user = User::factory()->create(['role' => 'buyer']);
    $note = Note::factory()->create([
        'sale_mode' => 'scarcity',
        'user_id' => $user->id, // User still owns it
    ]);

    expect($note->getRepurchasePrice($user->id))->toBeNull();
});

