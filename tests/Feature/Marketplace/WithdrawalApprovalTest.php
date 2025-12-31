<?php

namespace Tests\Feature\Marketplace;

use App\Models\User;
use App\Models\Withdrawal;
use App\Services\BalanceService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WithdrawalApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;
    protected User $admin;
    protected BalanceService $balanceService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seller = User::factory()->create([
            'role' => 'user',
            'balance' => 500000,
        ]);
        
        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->balanceService = app(BalanceService::class);
    }

    /** @test */
    public function user_can_create_withdrawal_request()
    {
        $this->actingAs($this->seller);

        $response = $this->post(route('marketplace.withdrawals.store'), [
            'amount' => 100000,
            'method' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'user_type' => 'seller',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('withdrawals', [
            'user_id' => $this->seller->id,
            'amount' => 100000,
            'method' => 'bank_transfer',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function withdrawal_request_requires_minimum_amount()
    {
        $this->actingAs($this->seller);

        $response = $this->post(route('marketplace.withdrawals.store'), [
            'amount' => 40000, // Below minimum 50000
            'method' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'user_type' => 'seller',
        ]);

        $response->assertSessionHasErrors('amount');
    }

    /** @test */
    public function admin_can_approve_withdrawal()
    {
        Notification::fake();

        $withdrawal = Withdrawal::create([
            'user_id' => $this->seller->id,
            'amount' => 100000,
            'method' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'status' => 'pending',
            'user_type' => 'seller',
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('withdrawals.approve', $withdrawal), [
            'admin_notes' => 'Approved for processing',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $withdrawal->refresh();
        $this->assertEquals('approved', $withdrawal->status);
        $this->assertEquals($this->admin->id, $withdrawal->admin_id);
        $this->assertEquals('Approved for processing', $withdrawal->admin_notes);
    }

    /** @test */
    public function admin_can_reject_withdrawal()
    {
        Notification::fake();

        $withdrawal = Withdrawal::create([
            'user_id' => $this->seller->id,
            'amount' => 100000,
            'method' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'status' => 'pending',
            'user_type' => 'seller',
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('withdrawals.reject', $withdrawal), [
            'admin_notes' => 'Insufficient documentation',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $withdrawal->refresh();
        $this->assertEquals('rejected', $withdrawal->status);
        $this->assertEquals($this->admin->id, $withdrawal->admin_id);
        $this->assertEquals('Insufficient documentation', $withdrawal->admin_notes);
    }

    /** @test */
    public function admin_can_complete_withdrawal()
    {
        Notification::fake();

        $withdrawal = Withdrawal::create([
            'user_id' => $this->seller->id,
            'amount' => 100000,
            'method' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'status' => 'approved',
            'user_type' => 'seller',
        ]);

        $initialBalance = $this->seller->balance;

        $this->actingAs($this->admin);

        $response = $this->post(route('admin.withdrawals.complete', $withdrawal));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $withdrawal->refresh();
        $this->assertEquals('completed', $withdrawal->status);
        $this->assertNotNull($withdrawal->processed_at);

        // Balance should be deducted
        $this->seller->refresh();
        $this->assertEquals($initialBalance - 100000, $this->seller->balance);

        // Transaction record should be created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->seller->id,
            'type' => 'withdrawal',
            'amount' => 100000,
            'status' => 'completed',
            'reference_id' => $withdrawal->id,
        ]);
    }

    /** @test */
    public function withdrawal_cannot_be_completed_if_not_approved()
    {
        $withdrawal = Withdrawal::create([
            'user_id' => $this->seller->id,
            'amount' => 100000,
            'method' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'status' => 'pending',
            'user_type' => 'seller',
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('admin.withdrawals.complete', $withdrawal));

        $response->assertSessionHasErrors('error');
        $this->assertEquals('pending', $withdrawal->fresh()->status);
    }

    /** @test */
    public function admin_can_view_withdrawal_list()
    {
        Withdrawal::create([
            'user_id' => $this->seller->id,
            'amount' => 100000,
            'method' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'status' => 'pending',
            'user_type' => 'seller',
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('withdrawals.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_filter_withdrawals_by_status()
    {
        Withdrawal::create([
            'user_id' => $this->seller->id,
            'amount' => 100000,
            'method' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'status' => 'pending',
            'user_type' => 'seller',
        ]);

        Withdrawal::create([
            'user_id' => $this->seller->id,
            'amount' => 200000,
            'method' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'status' => 'approved',
            'user_type' => 'seller',
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('withdrawals.index', ['status' => 'pending']));

        $response->assertStatus(200);
        $response->assertViewHas('withdrawals', function ($withdrawals) {
            return $withdrawals->every(function ($withdrawal) {
                return $withdrawal->status === 'pending';
            });
        });
    }

    /** @test */
    public function user_cannot_withdraw_more_than_balance()
    {
        $this->actingAs($this->seller);

        $response = $this->post(route('marketplace.withdrawals.store'), [
            'amount' => 600000, // More than balance (500000)
            'method' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'user_type' => 'seller',
        ]);

        // Should fail validation or business logic check
        $response->assertSessionHasErrors();
    }

    /** @test */
    public function withdrawal_sends_notification_to_admin()
    {
        Notification::fake();

        $withdrawal = Withdrawal::create([
            'user_id' => $this->seller->id,
            'amount' => 100000,
            'method' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'status' => 'pending',
            'user_type' => 'seller',
        ]);

        $notificationService = app(NotificationService::class);
        $notificationService->notifyWithdrawalRequest($withdrawal);

        Notification::assertSentTo(
            $this->admin,
            \App\Notifications\WithdrawalRequestNotification::class
        );
    }
}

