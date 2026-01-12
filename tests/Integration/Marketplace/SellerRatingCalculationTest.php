<?php

namespace Tests\Integration\Marketplace;

use Tests\TestCase;
use App\Models\User;
use App\Services\SellerRatingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SellerRatingCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected SellerRatingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SellerRatingService::class);
    }

    /** @test */
    public function it_calculates_weighted_composite_rating()
    {
        $seller = User::factory()->create();

        $rating = $this->service->calculateSellerRating($seller);

        $this->assertIsNumeric($rating);
        $this->assertGreaterThanOrEqual(0, $rating);
        $this->assertLessThanOrEqual(5, $rating);
    }

    /** @test */
    public function it_updates_seller_rating_in_cache()
    {
        $seller = User::factory()->create();

        $this->service->updateSellerRating($seller);

        $seller->refresh();
        $this->assertNotNull($seller->seller_rating);
    }

    /** @test */
    public function it_provides_rating_breakdown()
    {
        $seller = User::factory()->create();

        $breakdown = $this->service->getRatingBreakdown($seller);

        $this->assertIsArray($breakdown);
        $this->assertArrayHasKey('overall_rating', $breakdown);
        $this->assertArrayHasKey('review_rating', $breakdown);
        $this->assertArrayHasKey('fulfillment_rating', $breakdown);
        $this->assertArrayHasKey('response_rating', $breakdown);
        $this->assertArrayHasKey('total_reviews', $breakdown);
    }
}

