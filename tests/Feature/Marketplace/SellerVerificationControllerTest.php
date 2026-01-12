<?php

namespace Tests\Feature\Marketplace;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SellerVerificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seller = User::factory()->create();
    }

    /** @test */
    public function it_can_show_verification_page()
    {
        $response = $this->actingAs($this->seller)
            ->get(route('marketplace.seller.verification'));

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_get_verification_status()
    {
        $response = $this->actingAs($this->seller)
            ->get(route('marketplace.seller.verification.status'));

        $response->assertStatus(200);
    }
}

