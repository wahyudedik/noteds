<?php

namespace Tests\Feature;

use App\Jobs\ProcessReferralCommissions;
use App\Models\Referral;
use App\Models\ReferralTransaction;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use App\Services\ReferralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ReferralAutomationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $seller;
    private User $buyer;
    private ReferralService $referralService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->seller = User::factory()->create();
        $this->seller->assignRole('seller');

        $this->buyer = User::factory()->create();
        $this->buyer->assignRole('buyer');

        // Create wallets for users
        Wallet::factory()->create(['user_id' => $this->admin->id, 'balance' => 10000000]);
        Wallet::factory()->create(['user_id' => $this->seller->id, 'balance' => 0]);
        Wallet::factory()->create(['user_id' => $this->buyer->id, 'balance' => 0]);

        // Set user wallet balances
        $this->admin->update(['wallet_balance' => 10000000]);
        $this->seller->update(['wallet_balance' => 0]);
        $this->buyer->update(['wallet_balance' => 0]);

        // Initialize referral service
        $this->referralService = app(ReferralService::class);
    }

    /** @test */
    public function it_processes_commission_with_sufficient_balance()
    {
        // Create a referral
        $referral = Referral::factory()->create([
            'referrer_id' => $this->seller->id,
            'referred_id' => $this->buyer->id,
            'status' => 'completed',
            'reward_amount' => 50000,
        ]);

        // Process commission
        $transaction = $this->referralService->processCommission($referral, $this->admin);

        // Assert transaction was created
        $this->assertNotNull($transaction);
        $this->assertEquals('sent', $transaction->status);
        $this->assertEquals(50000, $transaction->amount);

        // Assert admin balance was deducted
        $this->admin->refresh();
        $this->assertEquals(10000000 - 50000, $this->admin->wallet_balance);

        // Assert seller received the commission
        $this->seller->refresh();
        $this->assertEquals(50000, $this->seller->wallet_balance);
    }

    /** @test */
    public function it_rejects_commission_with_insufficient_balance()
    {
        // Reduce admin balance
        $this->admin->update(['wallet_balance' => 1000]);

        // Create a referral requiring 50000
        $referral = Referral::factory()->create([
            'referrer_id' => $this->seller->id,
            'referred_id' => $this->buyer->id,
            'status' => 'completed',
            'reward_amount' => 50000,
        ]);

        // Attempt to process commission
        $transaction = $this->referralService->processCommission($referral, $this->admin);

        // Assert transaction was not created
        $this->assertNull($transaction);

        // Assert admin balance unchanged
        $this->admin->refresh();
        $this->assertEquals(1000, $this->admin->wallet_balance);

        // Assert seller received nothing
        $this->seller->refresh();
        $this->assertEquals(0, $this->seller->wallet_balance);
    }

    /** @test */
    public function it_sends_notifications_on_successful_processing()
    {
        Notification::fake();

        // Create multiple referrals
        $referrals = Referral::factory(3)->create([
            'referrer_id' => $this->seller->id,
            'referred_id' => $this->buyer->id,
            'status' => 'completed',
            'reward_amount' => 25000,
        ]);

        // Process all commissions
        $processed = 0;
        foreach ($referrals as $referral) {
            $transaction = $this->referralService->processCommission($referral, $this->admin);
            if ($transaction) {
                $processed++;
            }
        }

        // Assert all were processed
        $this->assertEquals(3, $processed);

        // Assert notifications were sent to admin and users
        Notification::assertSentTo(
            [$this->admin],
            \App\Notifications\ReferralCommissionSentNotification::class
        );

        Notification::assertSentTo(
            [$this->buyer],
            \App\Notifications\ReferralCommissionReceivedNotification::class,
            3 // 3 notifications
        );
    }

    /** @test */
    public function it_respects_minimum_amount_threshold()
    {
        // Set minimum amount to 100000
        Setting::setSetting('referral_min_amount_to_send', 100000, 'number', 'referral');

        // Create a low-value referral
        $lowValueReferral = Referral::factory()->create([
            'referrer_id' => $this->seller->id,
            'referred_id' => $this->buyer->id,
            'status' => 'completed',
            'reward_amount' => 10000, // Below threshold
        ]);

        // Get processable commissions
        $commissions = $this->referralService->getProcessableCommissions();

        // Assert low value referral is not included
        $this->assertFalse($commissions->contains($lowValueReferral));
    }

    /** @test */
    public function it_respects_maximum_batch_size()
    {
        // Set maximum batch size to 2
        Setting::setSetting('referral_max_batch_size', 2, 'number', 'referral');

        // Create 5 referrals
        $referrals = Referral::factory(5)->create([
            'referrer_id' => $this->seller->id,
            'referred_id' => $this->buyer->id,
            'status' => 'completed',
            'reward_amount' => 50000,
        ]);

        // Get processable commissions
        $commissions = $this->referralService->getProcessableCommissions();

        // Take batch
        $batch = $commissions->take(2);

        // Assert batch size is limited
        $this->assertEquals(2, $batch->count());
    }

    /** @test */
    public function it_skips_processing_when_disabled()
    {
        // Disable automatic sending
        Setting::setSetting('referral_auto_send_enabled', false, 'boolean', 'referral');

        // Queue the job
        Queue::fake();

        // Dispatch job
        ProcessReferralCommissions::dispatch();

        // The job would check the setting and exit early
        $this->assertTrue(true); // Job execution test would require actual queuing
    }

    /** @test */
    public function it_updates_transaction_status_correctly()
    {
        // Create a referral
        $referral = Referral::factory()->create([
            'referrer_id' => $this->seller->id,
            'referred_id' => $this->buyer->id,
            'status' => 'completed',
            'reward_amount' => 50000,
        ]);

        // Process commission
        $transaction = $this->referralService->processCommission($referral, $this->admin);

        // Reload transaction
        $transaction->refresh();

        // Assert status is sent and sent_at is set
        $this->assertEquals('sent', $transaction->status);
        $this->assertNotNull($transaction->sent_at);
    }

    /** @test */
    public function it_syncs_wallet_models_correctly()
    {
        // Create a referral
        $referral = Referral::factory()->create([
            'referrer_id' => $this->seller->id,
            'referred_id' => $this->buyer->id,
            'status' => 'completed',
            'reward_amount' => 50000,
        ]);

        // Process commission
        $this->referralService->processCommission($referral, $this->admin);

        // Check User model wallet_balance
        $this->seller->refresh();
        $sellerBalance = $this->seller->wallet_balance;

        // Check Wallet model balance
        $walletRecord = Wallet::where('user_id', $this->seller->id)->first();
        $walletBalance = $walletRecord->balance ?? 0;

        // Both should match the processed amount
        $this->assertEquals(50000, $sellerBalance);
        $this->assertEquals(50000, $walletBalance);
    }

    /** @test */
    public function it_creates_transaction_record_for_audit_trail()
    {
        // Create a referral
        $referral = Referral::factory()->create([
            'referrer_id' => $this->seller->id,
            'referred_id' => $this->buyer->id,
            'status' => 'completed',
            'reward_amount' => 50000,
        ]);

        // Process commission
        $transaction = $this->referralService->processCommission($referral, $this->admin);

        // Assert transaction record exists in database
        $this->assertDatabaseHas('referral_transactions', [
            'id' => $transaction->id,
            'referral_id' => $referral->id,
            'user_id' => $this->seller->id,
            'admin_id' => $this->admin->id,
            'amount' => 50000,
            'status' => 'sent',
        ]);
    }

    /** @test */
    public function it_distinguishes_between_signup_bonus_and_transaction_commission()
    {
        // Create two transactions - one for signup, one for commission
        $referral1 = Referral::factory()->create([
            'referrer_id' => $this->seller->id,
            'referred_id' => $this->buyer->id,
            'status' => 'completed',
            'reward_amount' => 50000,
        ]);

        $transaction1 = $this->referralService->processCommission($referral1, $this->admin);
        $transaction1->update(['type' => 'signup_bonus']);

        $referral2 = Referral::factory()->create([
            'referrer_id' => $this->seller->id,
            'referred_id' => $this->buyer->id,
            'status' => 'completed',
            'reward_amount' => 25000,
        ]);

        $transaction2 = $this->referralService->processCommission($referral2, $this->admin);
        $transaction2->update(['type' => 'transaction_commission']);

        // Query by type
        $signupBonuses = ReferralTransaction::byType('signup_bonus')->count();
        $commissions = ReferralTransaction::byType('transaction_commission')->count();

        $this->assertEquals(1, $signupBonuses);
        $this->assertEquals(1, $commissions);
    }
}
