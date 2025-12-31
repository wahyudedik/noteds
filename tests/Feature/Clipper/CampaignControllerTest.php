<?php

namespace Tests\Feature\Clipper;

use Tests\TestCase;
use App\Models\User;
use App\Models\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CampaignControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_create_campaign()
    {
        $response = $this->post(route('clipper.campaigns.store'), [
            'title' => 'Test Campaign',
            'description' => 'Test description',
            'cpm' => 5000,
            'max_budget' => 1000000,
            'duration_days' => 30,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('campaigns', [
            'creator_id' => $this->user->id,
            'title' => 'Test Campaign',
            'status' => 'draft',
        ]);
    }

    /** @test */
    public function it_can_activate_campaign()
    {
        $campaign = Campaign::factory()->create([
            'creator_id' => $this->user->id,
            'status' => 'draft',
        ]);

        // First, ensure creator has balance
        $creatorWallet = app(\App\Services\WalletService::class)->getCreatorWallet($this->user);
        $creatorWallet->addBalance($campaign->max_budget);

        $response = $this->post(route('clipper.campaigns.activate', $campaign));

        $response->assertRedirect();
        $campaign->refresh();
        $this->assertEquals('active', $campaign->status);
    }

    /** @test */
    public function it_can_share_campaign_as_post()
    {
        $campaign = Campaign::factory()->create([
            'creator_id' => $this->user->id,
        ]);

        $response = $this->post(route('clipper.campaigns.share', $campaign), [
            'message' => 'Check out my new campaign!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'user_id' => $this->user->id,
            'campaign_id' => $campaign->id,
        ]);
    }
}

