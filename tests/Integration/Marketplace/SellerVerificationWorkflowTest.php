<?php

namespace Tests\Integration\Marketplace;

use Tests\TestCase;
use App\Models\User;
use App\Services\SellerVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

class SellerVerificationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected SellerVerificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SellerVerificationService::class);
        Storage::fake('local');
        Event::fake();
    }

    /** @test */
    public function it_completes_verification_workflow()
    {
        $seller = User::factory()->create(['email_verified_at' => now()]);
        $admin = User::factory()->create(['is_admin' => true]);

        // Check eligibility
        $canApply = $this->service->canApply($seller);
        $this->assertIsArray($canApply);

        // Apply for verification
        $verification = $this->service->applyForVerification($seller, [
            'business_name' => 'Test Business',
            'business_type' => 'Retail',
            'business_address' => '123 Test Street',
            'tax_id' => '1234567890',
            'documents' => [],
            'additional_info' => 'Test info',
        ]);

        $this->assertNotNull($verification);
        $this->assertEquals('pending', $verification->status);

        // Approve verification
        $this->service->approveVerification($verification, $admin, 'Approved');

        $seller->refresh();
        $verification->refresh();

        $this->assertTrue($seller->is_verified_seller);
        $this->assertEquals('approved', $verification->status);
    }

    /** @test */
    public function it_can_reject_and_revoke_verification()
    {
        $seller = User::factory()->create(['email_verified_at' => now(), 'is_verified_seller' => true]);
        $admin = User::factory()->create(['is_admin' => true]);

        // Create approved verification
        $verification = $this->service->applyForVerification($seller, [
            'business_name' => 'Test Business',
            'business_type' => 'Retail',
            'business_address' => '123 Test Street',
        ]);
        $this->service->approveVerification($verification, $admin);

        // Revoke verification
        $this->service->revokeVerification($seller, $admin, 'Revoked for test');

        $seller->refresh();

        $this->assertFalse($seller->is_verified_seller);
    }
}

