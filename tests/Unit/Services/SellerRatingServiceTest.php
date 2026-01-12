<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Services\SellerRatingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SellerRatingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SellerRatingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SellerRatingService::class);
    }

    /** @test */
    public function it_can_calculate_seller_rating()
    {
        $seller = User::factory()->create();

        $rating = $this->service->calculateSellerRating($seller);

        $this->assertIsNumeric($rating);
        $this->assertGreaterThanOrEqual(0, $rating);
        $this->assertLessThanOrEqual(5, $rating);
    }

    /** @test */
    public function it_can_get_rating_breakdown()
    {
        $seller = User::factory()->create();

        $breakdown = $this->service->getRatingBreakdown($seller);

        $this->assertIsArray($breakdown);
        $this->assertArrayHasKey('overall_rating', $breakdown);
        $this->assertArrayHasKey('review_rating', $breakdown);
        $this->assertArrayHasKey('fulfillment_rating', $breakdown);
        $this->assertArrayHasKey('response_rating', $breakdown);
    }

    /** @test */
    public function it_can_get_rating_weights()
    {
        $weights = $this->service->getRatingWeights();

        $this->assertIsArray($weights);
        $this->assertArrayHasKey('review', $weights);
        $this->assertArrayHasKey('fulfillment', $weights);
        $this->assertArrayHasKey('response_time', $weights);
    }
}

