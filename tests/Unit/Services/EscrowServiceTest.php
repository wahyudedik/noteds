<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Models\Campaign;
use App\Services\EscrowService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EscrowServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EscrowService $escrowService;
    protected WalletService $walletService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->escrowService = app(EscrowService::class);
        $this->walletService = app(WalletService::class);
    }

    /** @test */
    public function it_can_lock_campaign_budget()
    {
        $creator = User::factory()->create();
        $creatorWallet = $this->walletService->getCreatorWallet($creator);
        $creatorWallet->addBalance(1000000);
        
        $campaign = Campaign::factory()->create([
            'creator_id' => $creator->id,
            'max_budget' => 500000,
            'status' => 'draft',
        ]);

        $result = $this->escrowService->lockCampaignBudget($campaign);

        $this->assertTrue($result);
        $campaignWallet = $this->walletService->getCampaignWallet($campaign);
        $this->assertEquals(500000, $campaignWallet->total_budget);
        $this->assertEquals(500000, $campaignWallet->locked_amount);
    }

    /** @test */
    public function it_cannot_lock_budget_with_insufficient_balance()
    {
        $creator = User::factory()->create();
        
        $campaign = Campaign::factory()->create([
            'creator_id' => $creator->id,
            'max_budget' => 1000000,
            'status' => 'draft',
        ]);

        $result = $this->escrowService->lockCampaignBudget($campaign);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_refund_remaining_budget()
    {
        $creator = User::factory()->create();
        $creatorWallet = $this->walletService->getCreatorWallet($creator);
        $creatorWallet->addBalance(1000000);
        
        $campaign = Campaign::factory()->create([
            'creator_id' => $creator->id,
            'max_budget' => 1000000,
            'status' => 'active',
        ]);

        $this->escrowService->lockCampaignBudget($campaign);
        
        $campaignWallet = $this->walletService->getCampaignWallet($campaign);
        $campaignWallet->deductBudget(300000); // Simulate spending

        $result = $this->escrowService->refundRemainingBudget($campaign);

        $this->assertTrue($result);
        $creatorWallet->refresh();
        $this->assertEquals(700000, $creatorWallet->balance_available);
    }
}

