<?php

namespace Tests\Feature\Marketplace;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InventoryManagementControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seller = User::factory()->create();
    }

    /** @test */
    public function it_can_list_seller_products()
    {
        $product = Product::create([
            'user_id' => $this->seller->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => 100000,
            'stock' => 10,
        ]);

        $response = $this->actingAs($this->seller)
            ->get(route('marketplace.seller.inventory.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_show_product_stock_details()
    {
        $product = Product::create([
            'user_id' => $this->seller->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => 100000,
            'stock' => 10,
        ]);

        $response = $this->actingAs($this->seller)
            ->get(route('marketplace.seller.inventory.show', $product));

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_update_product_stock()
    {
        $product = Product::create([
            'user_id' => $this->seller->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => 100000,
            'stock' => 10,
        ]);

        $response = $this->actingAs($this->seller)
            ->put(route('marketplace.seller.inventory.stock.update', $product), [
                'quantity' => 5,
                'type' => 'adjustment',
                'reason' => 'Test adjustment',
            ]);

        $response->assertStatus(200);
        $product->refresh();
        $this->assertEquals(15, $product->stock);
    }
}

