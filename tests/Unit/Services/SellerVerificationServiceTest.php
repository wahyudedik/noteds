<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Services\SellerVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SellerVerificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SellerVerificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SellerVerificationService::class);
    }

    /** @test */
    public function it_can_check_eligibility()
    {
        $seller = User::factory()->create();

        $result = $this->service->canApply($seller);

        $this->assertIsArray($result);
    }

    /** @test */
    public function it_can_get_verification_status()
    {
        $seller = User::factory()->create();

        $status = $this->service->getVerificationStatus($seller);

        $this->assertNull($status); // No verification yet
    }
}

