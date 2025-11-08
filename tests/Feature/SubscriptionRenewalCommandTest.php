<?php

use App\Mail\SubscriptionRenewalFailedMail;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\artisan;

test('subscription renewal command handles sufficient and insufficient balances', function () {
    Mail::fake();

    Carbon::setTestNow(now());

    Setting::setSetting('premium_price_monthly', 100000, 'number', 'subscription', 'Premium monthly price for tests.');

    $successUser = User::factory()->create([
        'wallet_balance' => 150000,
    ]);
    Wallet::updateOrCreate(
        ['user_id' => $successUser->id],
        [
            'balance' => 150000,
            'currency' => config('currency.base_currency', 'IDR'),
        ]
    );
    $successSubscription = Subscription::create([
        'user_id' => $successUser->id,
        'plan' => 'premium',
        'status' => 'active',
        'expired_at' => now(),
    ]);

    $failedUser = User::factory()->create([
        'wallet_balance' => 25000,
    ]);
    Wallet::updateOrCreate(
        ['user_id' => $failedUser->id],
        [
            'balance' => 25000,
            'currency' => config('currency.base_currency', 'IDR'),
        ]
    );
    $failedSubscription = Subscription::create([
        'user_id' => $failedUser->id,
        'plan' => 'premium',
        'status' => 'active',
        'expired_at' => now()->subDay(),
    ]);

    artisan('subscriptions:renew')->assertExitCode(0);

    $successUser->refresh();
    $failedUser->refresh();
    $successSubscription->refresh();
    $failedSubscription->refresh();

    expect((float) $successUser->wallet_balance)->toBe(50000.0)
        ->and($successSubscription->status)->toBe('active')
        ->and($successSubscription->expired_at->greaterThan(Carbon::now()->addWeeks(3)))->toBeTrue()
        ->and(Transaction::where('buyer_id', $successUser->id)->where('notes', 'Premium subscription auto-renewal')->count())->toBe(1);

    expect((float) $failedUser->wallet_balance)->toBe(25000.0)
        ->and($failedSubscription->status)->toBe('expired')
        ->and(Transaction::where('buyer_id', $failedUser->id)->where('notes', 'Premium subscription auto-renewal')->count())->toBe(0);

    Mail::assertSent(SubscriptionRenewalFailedMail::class, function (SubscriptionRenewalFailedMail $mail) use ($failedUser) {
        return $mail->hasTo($failedUser->email);
    });

    Carbon::setTestNow();
});


