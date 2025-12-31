<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Clip;
use App\Services\TopUpService;
use App\Services\CampaignService;
use App\Services\ClipService;
use App\Services\AutoTransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EscrowFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_completes_full_escrow_flow()
    {
        // 1. Create Brand and top up
        $brand = User::factory()->create();
        $topUpService = app(TopUpService::class);
        // Simulate successful top up
        $walletService = app(\App\Services\WalletService::class);
        $walletService->addUserBalance($brand, 1000000, 'Top up');

        // 2. Create campaign
        $campaignService = app(CampaignService::class);
        $campaign = $campaignService->createCampaign($brand, [
            'title' => 'Test Campaign',
            'description' => 'Test',
            'cpm' => 5000,
            'max_budget' => 500000,
            'duration_days' => 30,
        ]);

        // 3. Activate campaign (lock budget)
        $campaignService->activateCampaign($campaign);
        $campaign->refresh();
        $this->assertEquals('active', $campaign->status);

        // 4. Clipper submits clip
        $clipper = User::factory()->create();
        $clipService = app(ClipService::class);
        $clip = $clipService->submitClip($clipper, $campaign, [
            'content_url' => 'https://www.youtube.com/watch?v=test',
            'platform' => 'youtube',
            'platform_content_id' => 'test',
        ]);

        $this->assertEquals('pending', $clip->status);

        // 5. Approve clip (simulate view validation)
        $clip->update([
            'status' => 'approved',
            'valid_views' => 10000,
            'approved_reward' => 50000, // 10000 views * 5000 CPM / 1000
        ]);

        // 6. Auto transfer reward
        $autoTransferService = app(AutoTransferService::class);
        $result = $autoTransferService->transferRewardToClipper($clip);

        $this->assertTrue($result);
        $clip->refresh();
        $this->assertNotNull($clip->paid_at);

        // 7. Verify balances
        $clipperWallet = $walletService->getClipperWallet($clipper);
        $this->assertGreaterThan(0, $clipperWallet->balance_pending);

        $campaignWallet = $walletService->getCampaignWallet($campaign);
        $this->assertLessThan($campaign->max_budget, $campaignWallet->remaining_budget);
    }
}

