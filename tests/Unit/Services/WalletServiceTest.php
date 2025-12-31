<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Services\WalletService;
use App\Services\BalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WalletServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WalletService $walletService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->walletService = app(WalletService::class);
    }

    /** @test */
    public function it_can_get_or_create_creator_wallet()
    {
        $user = User::factory()->create();

        $wallet = $this->walletService->getCreatorWallet($user);

        $this->assertNotNull($wallet);
        $this->assertEquals($user->id, $wallet->user_id);
        $this->assertEquals(0, $wallet->balance_available);
    }

    /** @test */
    public function it_can_get_or_create_clipper_wallet()
    {
        $user = User::factory()->create();

        $wallet = $this->walletService->getClipperWallet($user);

        $this->assertNotNull($wallet);
        $this->assertEquals($user->id, $wallet->user_id);
        $this->assertEquals(0, $wallet->balance_available);
    }

    /** @test */
    public function it_can_add_balance_to_user()
    {
        $user = User::factory()->create();
        $amount = 100000;

        $result = $this->walletService->addUserBalance($user, $amount, 'Test top up');

        $this->assertTrue($result);
        $wallet = $this->walletService->getCreatorWallet($user);
        $this->assertEquals($amount, $wallet->balance_available);
    }

    /** @test */
    public function it_can_deduct_balance_from_user()
    {
        $user = User::factory()->create();
        $this->walletService->addUserBalance($user, 100000, 'Test top up');

        $result = $this->walletService->deductUserBalance($user, 50000, 'Test deduction');

        $this->assertTrue($result);
        $wallet = $this->walletService->getCreatorWallet($user);
        $this->assertEquals(50000, $wallet->balance_available);
    }

    /** @test */
    public function it_throws_exception_when_insufficient_balance()
    {
        $user = User::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient balance');

        $this->walletService->deductUserBalance($user, 100000, 'Test deduction');
    }
}

