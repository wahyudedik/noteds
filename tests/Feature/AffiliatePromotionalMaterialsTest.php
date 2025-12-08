<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AffiliateLink;
use App\Models\AffiliatePromotionalMaterial;
use Database\Seeders\AffiliatePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AffiliatePromotionalMaterialsTest extends TestCase
{
    use RefreshDatabase;

    protected User $affiliate;
    protected AffiliateLink $link;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        
        $this->seed([RoleSeeder::class, AffiliatePermissionSeeder::class]);
        
        $this->affiliate = User::factory()->create();
        $this->affiliate->givePermissionTo('create affiliate links');
        
        $this->link = AffiliateLink::factory()->create(['user_id' => $this->affiliate->id]);
    }

    /** @test */
    public function user_can_create_promotional_material()
    {
        $file = UploadedFile::fake()->image('banner.jpg', 728, 90);
        
        $response = $this->actingAs($this->affiliate)->post(
            route('affiliate.promotional-materials.store', $this->link),
            [
                'name' => 'Test Banner',
                'type' => 'banner',
                'size' => '728x90',
                'image' => $file,
                'description' => 'Test banner description',
            ]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('affiliate_promotional_materials', [
            'name' => 'Test Banner',
            'type' => 'banner',
            'affiliate_link_id' => $this->link->id,
        ]);
    }

    /** @test */
    public function user_can_create_html_promotional_material()
    {
        $response = $this->actingAs($this->affiliate)->post(
            route('affiliate.promotional-materials.store', $this->link),
            [
                'name' => 'Text Ad',
                'type' => 'text',
                'html_code' => '<a href="https://example.com">Click here</a>',
                'description' => 'Simple text ad',
            ]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('affiliate_promotional_materials', [
            'name' => 'Text Ad',
            'type' => 'text',
            'affiliate_link_id' => $this->link->id,
        ]);
    }

    /** @test */
    public function user_can_update_promotional_material()
    {
        $material = AffiliatePromotionalMaterial::factory()->create([
            'affiliate_link_id' => $this->link->id,
        ]);
        
        $response = $this->actingAs($this->affiliate)->put(
            route('affiliate.promotional-materials.update', $material),
            [
                'name' => 'Updated Material',
                'type' => 'text',
                'html_code' => '<a href="#">Updated</a>',
                'description' => 'Updated description',
            ]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('affiliate_promotional_materials', [
            'id' => $material->id,
            'name' => 'Updated Material',
        ]);
    }

    /** @test */
    public function user_can_delete_promotional_material()
    {
        $material = AffiliatePromotionalMaterial::factory()->create([
            'affiliate_link_id' => $this->link->id,
        ]);
        
        $response = $this->actingAs($this->affiliate)->delete(
            route('affiliate.promotional-materials.delete', $material)
        );

        $response->assertRedirect();
        $this->assertDatabaseMissing('affiliate_promotional_materials', [
            'id' => $material->id,
        ]);
    }

    /** @test */
    public function user_can_only_manage_own_materials()
    {
        $otherUser = User::factory()->create();
        $otherLink = AffiliateLink::factory()->create(['user_id' => $otherUser->id]);
        $material = AffiliatePromotionalMaterial::factory()->create([
            'affiliate_link_id' => $otherLink->id,
        ]);
        
        $response = $this->actingAs($this->affiliate)->put(
            route('affiliate.promotional-materials.update', $material),
            [
                'name' => 'Hacked Material',
                'type' => 'text',
            ]
        );

        $response->assertForbidden();
        $this->assertDatabaseMissing('affiliate_promotional_materials', [
            'id' => $material->id,
            'name' => 'Hacked Material',
        ]);
    }

    /** @test */
    public function image_file_size_cannot_exceed_2mb()
    {
        $file = UploadedFile::fake()->create('banner.jpg', 2100); // 2.1 MB
        
        $response = $this->actingAs($this->affiliate)->post(
            route('affiliate.promotional-materials.store', $this->link),
            [
                'name' => 'Large Banner',
                'type' => 'banner',
                'image' => $file,
            ]
        );

        $response->assertSessionHasErrors('image');
    }

    /** @test */
    public function promotional_material_validates_required_fields()
    {
        $response = $this->actingAs($this->affiliate)->post(
            route('affiliate.promotional-materials.store', $this->link),
            []
        );

        $response->assertSessionHasErrors(['name', 'type']);
    }
}
