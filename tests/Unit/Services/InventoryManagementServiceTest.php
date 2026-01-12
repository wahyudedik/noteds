<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Services\InventoryManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InventoryManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InventoryManagementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(InventoryManagementService::class);
    }

    /** @test */
    public function it_can_record_stock_sale()
    {
        $seller = User::factory()->create();
        $product = Product::create([
            'user_id' => $seller->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => 100000,
            'stock' => 10,
        ]);

        $history = $this->service->recordSale($product, null);

        $this->assertNotNull($history);
        $this->assertEquals(-1, $history->quantity_change);
        $this->assertEquals(9, $history->new_stock_level);
        $product->refresh();
        $this->assertEquals(9, $product->stock);
    }

    /** @test */
    public function it_can_record_restock()
    {
        $seller = User::factory()->create();
        $product = Product::create([
            'user_id' => $seller->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => 100000,
            'stock' => 10,
        ]);

        $history = $this->service->recordRestock($product, 5, 'Restock', $seller);

        $this->assertNotNull($history);
        $this->assertEquals(5, $history->quantity_change);
        $this->assertEquals(15, $history->new_stock_level);
        $product->refresh();
        $this->assertEquals(15, $product->stock);
    }

    /** @test */
    public function it_can_check_low_stock()
    {
        $seller = User::factory()->create(['low_stock_alert_threshold' => 10]);
        $product = Product::create([
            'user_id' => $seller->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => 100000,
            'stock' => 5,
            'low_stock_threshold' => 10,
        ]);

        $isLowStock = $this->service->checkLowStock($product);

        $this->assertTrue($isLowStock);
    }

    /** @test */
    public function it_returns_false_when_not_low_stock()
    {
        $seller = User::factory()->create(['low_stock_alert_threshold' => 10]);
        $product = Product::create([
            'user_id' => $seller->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => 100000,
            'stock' => 15,
            'low_stock_threshold' => 10,
        ]);

        $isLowStock = $this->service->checkLowStock($product);

        $this->assertFalse($isLowStock);
    }
}

