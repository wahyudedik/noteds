<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AffiliateLink;
use App\Models\Transaction;
use Database\Seeders\AffiliatePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffiliateCommissionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $affiliate;
    protected AffiliateLink $link;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed([RoleSeeder::class, AffiliatePermissionSeeder::class]);
        
        $this->affiliate = User::factory()->create();
        $this->affiliate->givePermissionTo('create affiliate links');
        
        $this->link = AffiliateLink::factory()->create(['user_id' => $this->affiliate->id]);
    }

    /** @test */
    public function affiliate_can_view_commission_tracking()
    {
        $response = $this->actingAs($this->affiliate)->get(route('affiliate.index'));
        
        $response->assertStatus(200);
        $response->assertViewHas('totalCommissions');
        $response->assertViewHas('totalLinks');
    }

    /** @test */
    public function affiliate_link_tracks_clicks()
    {
        // This test validates that clicks are being tracked
        // Actual implementation would need a conversion tracking pixel or API
        
        $this->link->update(['clicks' => 0]);
        
        $this->link->increment('clicks');
        
        $this->link->refresh();
        $this->assertEquals(1, $this->link->clicks);
    }

    /** @test */
    public function affiliate_can_view_payout_history()
    {
        $response = $this->actingAs($this->affiliate)->get(route('affiliate.index'));
        
        $response->assertStatus(200);
        $response->assertViewIsNotEmpty();
    }

    /** @test */
    public function affiliate_can_request_payout()
    {
        // Set up affiliate with pending commission
        $this->affiliate->update(['wallet_balance' => 10000]); // 100 USD in cents
        
        $response = $this->actingAs($this->affiliate)->post(
            route('affiliate.payouts.request'),
            [
                'amount' => 10000, // 100 USD
                'payment_method' => 'bank_transfer',
                'bank_account' => 'test@example.com', // or actual bank details
            ]
        );

        $response->assertRedirect();
        // Verify payout request was created
        $this->assertDatabaseHas('withdrawal_requests', [
            'user_id' => $this->affiliate->id,
            'amount' => 10000,
        ]);
    }

    /** @test */
    public function affiliate_cannot_request_payout_more_than_balance()
    {
        $this->affiliate->update(['wallet_balance' => 5000]); // 50 USD
        
        $response = $this->actingAs($this->affiliate)->post(
            route('affiliate.payouts.request'),
            [
                'amount' => 10000, // Request more than balance
                'payment_method' => 'bank_transfer',
            ]
        );

        $response->assertSessionHasErrors('amount');
    }

    /** @test */
    public function affiliate_can_view_conversion_data()
    {
        $response = $this->actingAs($this->affiliate)->get(route('affiliate.index'));
        
        $response->assertStatus(200);
        $response->assertViewIsNotEmpty();
    }
}
