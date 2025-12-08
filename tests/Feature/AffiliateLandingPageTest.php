<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AffiliateLink;
use Database\Seeders\AffiliatePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffiliateLandingPageTest extends TestCase
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
    public function user_can_update_landing_page()
    {
        $content = '<h1>Welcome to My Affiliate Link</h1><p>Special offer inside!</p>';

        $response = $this->actingAs($this->affiliate)->put(
            route('affiliate.links.landing.update', $this->link),
            [
                'landing_page_content' => $content,
                'landing_page_slug' => 'my-special-offer',
            ]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('affiliate_links', [
            'id' => $this->link->id,
            'landing_page_content' => $content,
            'landing_page_slug' => 'my-special-offer',
        ]);
    }

    /** @test */
    public function landing_page_slug_is_auto_generated_from_content()
    {
        $content = '<h1>My Special Landing Page</h1>';

        $response = $this->actingAs($this->affiliate)->put(
            route('affiliate.links.landing.update', $this->link),
            [
                'landing_page_content' => $content,
                // No slug provided - should auto-generate
            ]
        );

        $response->assertRedirect();

        $this->link->refresh();
        $this->assertNotNull($this->link->landing_page_slug);
        // Slug should be generated from content
        $this->assertStringContainsString('special', strtolower($this->link->landing_page_slug));
    }

    /** @test */
    public function user_can_only_update_own_landing_page()
    {
        $otherUser = User::factory()->create();
        $otherLink = AffiliateLink::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->affiliate)->put(
            route('affiliate.links.landing.update', $otherLink),
            [
                'landing_page_content' => 'Hacked Content',
                'landing_page_slug' => 'hacked',
            ]
        );

        $response->assertForbidden();
        $this->assertDatabaseMissing('affiliate_links', [
            'id' => $otherLink->id,
            'landing_page_content' => 'Hacked Content',
        ]);
    }

    /** @test */
    public function landing_page_content_can_contain_html()
    {
        $htmlContent = <<<HTML
        <div class="hero">
            <h1>Exclusive Offer</h1>
            <p>Limited time only!</p>
            <button onclick="window.location.href='https://example.com'">Get Access</button>
        </div>
        HTML;

        $response = $this->actingAs($this->affiliate)->put(
            route('affiliate.links.landing.update', $this->link),
            [
                'landing_page_content' => $htmlContent,
                'landing_page_slug' => 'exclusive-offer',
            ]
        );

        $response->assertRedirect();
        $this->link->refresh();
        $this->assertStringContainsString('<h1>Exclusive Offer</h1>', $this->link->landing_page_content);
    }

    /** @test */
    public function landing_page_slug_must_be_unique_per_user()
    {
        $this->link->update(['landing_page_slug' => 'my-offer']);

        $newLink = AffiliateLink::factory()->create(['user_id' => $this->affiliate->id]);

        $response = $this->actingAs($this->affiliate)->put(
            route('affiliate.links.landing.update', $newLink),
            [
                'landing_page_content' => 'Different content',
                'landing_page_slug' => 'my-offer', // Duplicate slug for same user
            ]
        );

        $response->assertSessionHasErrors('landing_page_slug');
    }

    /** @test */
    public function user_can_clear_landing_page()
    {
        $this->link->update([
            'landing_page_content' => '<h1>Old Content</h1>',
            'landing_page_slug' => 'old-slug',
        ]);

        $response = $this->actingAs($this->affiliate)->put(
            route('affiliate.links.landing.update', $this->link),
            [
                'landing_page_content' => '',
                'landing_page_slug' => '',
            ]
        );

        $response->assertRedirect();
        $this->link->refresh();
        $this->assertNull($this->link->landing_page_content);
        $this->assertNull($this->link->landing_page_slug);
    }
}
