<?php

namespace Tests\Feature\Clipper;

use Tests\TestCase;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Clip;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClipControllerTest extends TestCase
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
    public function it_can_submit_clip()
    {
        $campaign = Campaign::factory()->create([
            'status' => 'active',
        ]);

        $response = $this->post(route('clipper.clips.store'), [
            'campaign_id' => $campaign->id,
            'content_url' => 'https://www.youtube.com/watch?v=test123',
            'platform' => 'youtube',
            'platform_content_id' => 'test123',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clips', [
            'clipper_id' => $this->user->id,
            'campaign_id' => $campaign->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function it_validates_url_for_ssrf_attacks()
    {
        $campaign = Campaign::factory()->create([
            'status' => 'active',
        ]);

        $response = $this->post(route('clipper.clips.store'), [
            'campaign_id' => $campaign->id,
            'content_url' => 'http://localhost/admin',
            'platform' => 'youtube',
        ]);

        $response->assertSessionHasErrors('content_url');
    }
}

