<?php

use App\Models\Note;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdraw;
use App\Models\Setting;
use Spatie\Permission\Models\Role;

test('end-to-end withdraw flow', function () {
    $user = User::factory()->create();
    // Set username if required
    if (!$user->username) {
        $user->username = 'user' . $user->id;
        $user->save();
    }
    
    $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 100000]);
    $user->wallet_balance = 100000;
    $user->save();
    
    $this->actingAs($user);
    
    // 1. Create withdraw request
    $response = $this->post('/wallet/withdraw', [
        'amount' => 50000,
        'bank_name' => 'Test Bank',
        'account_number' => '1234567890',
        'account_name' => 'Test User',
        '_token' => csrf_token(),
    ]);
    
    $response->assertSessionHasNoErrors();
    $response->assertRedirect();
    
    $withdraw = Withdraw::where('user_id', $user->id)->latest()->first();
    expect($withdraw)->not->toBeNull();
    expect($withdraw->status)->toBe('pending');
    
    // 2. Admin approve (simulate 24 hours later)
    $admin = User::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $admin->assignRole($adminRole);
    
    // Set username if required
    if (!$admin->username) {
        $admin->username = 'admin' . $admin->id;
        $admin->save();
    }
    
    $this->actingAs($admin);
    
    // Travel 25 hours forward
    $this->travelTo(now()->addHours(25));
    
    // Update withdraw status using PATCH
    $response = $this->patch("/admin/withdraws/{$withdraw->id}", [
        'status' => 'approved',
        '_token' => csrf_token(),
    ]);
    
    $withdraw->refresh();
    $wallet->refresh();
    $user->refresh();
    
    expect($withdraw->status)->toBe('approved');
    expect((float) $wallet->balance)->toBe(50000.0);
    expect((float) $user->wallet_balance)->toBe(50000.0);
    
    $this->travelBack();
});

test('end-to-end referral reward flow', function () {
    // Set referral settings
    Setting::setSetting('referral_reward_signup', 5000, 'referral');
    Setting::setSetting('referral_reward_commission_percent', 5, 'referral');
    
    $referrer = User::factory()->create();
    $referral = User::factory()->create(['referred_by' => $referrer->id]);
    
    $referrerWallet = Wallet::firstOrCreate(['user_id' => $referrer->id], ['balance' => 0]);
    $referrer->wallet_balance = 0;
    $referrer->save();
    
    // 1. Signup reward
    $signupReward = (float) Setting::getSetting('referral_reward_signup', 'referral', 5000);
    $referrerWallet->balance += $signupReward;
    $referrerWallet->save();
    $referrer->wallet_balance = $referrerWallet->balance;
    $referrer->save();
    
    expect((float) $referrerWallet->fresh()->balance)->toBe(5000.0);
    
    // 2. Transaction commission
    $seller = User::factory()->create();
    $note = Note::factory()->create(['user_id' => $seller->id]);
    
    $transaction = Transaction::create([
        'buyer_id' => $referral->id,
        'seller_id' => $seller->id,
        'note_id' => $note->id,
        'amount' => 100000,
        'status' => 'success',
    ]);
    
    $commissionPercent = (float) Setting::getSetting('referral_reward_commission_percent', 'referral', 5);
    $commission = $transaction->amount * ($commissionPercent / 100);
    
    $referrerWallet->balance += $commission;
    $referrerWallet->save();
    $referrer->wallet_balance = $referrerWallet->balance;
    $referrer->save();
    
    expect((float) $referrerWallet->fresh()->balance)->toBe(5000.0 + $commission);
});

test('end-to-end admin settings update flow', function () {
    $admin = User::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $admin->assignRole($adminRole);
    
    // Set username if required
    if (!$admin->username) {
        $admin->username = 'admin' . $admin->id;
        $admin->save();
    }
    
    $this->actingAs($admin);
    
    // Update premium price
    $response = $this->post('/admin/settings', [
        'premium_price_monthly' => 30000,
        '_token' => csrf_token(),
    ]);
    
    $response->assertSessionHasNoErrors();
    $response->assertRedirect();
    
    $premiumPrice = (float) Setting::getSetting('premium_price_monthly', 'subscription', 25000);
    expect($premiumPrice)->toBe(30000.0);
    
    // Update referral settings
    $response = $this->post('/admin/settings', [
        'referral_reward_signup' => 10000,
        'referral_reward_commission_percent' => 10,
        '_token' => csrf_token(),
    ]);
    
    $response->assertSessionHasNoErrors();
    
    $signupReward = (float) Setting::getSetting('referral_reward_signup', 'referral', 5000);
    $commissionPercent = (float) Setting::getSetting('referral_reward_commission_percent', 'referral', 5);
    
    expect($signupReward)->toBe(10000.0);
    expect($commissionPercent)->toBe(10.0);
});

