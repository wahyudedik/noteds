<?php

namespace Tests\Integration\Marketplace;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Services\InventoryManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

class LowStockDetectionTest extends TestCase
{
    use RefreshDatabase;

    protected InventoryManagementService $inventoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inventoryService = app(InventoryManagementService::class);
        Event::fake();
        Notification::fake();
    }

    /** @test */
    public function it_detects_low_stock_when_threshold_reached()
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

        // Reduce stock below threshold
        $this->inventoryService->updateStock($product, -6, 'sale', null, null, $seller);

        $isLowStock = $this->inventoryService->checkLowStock($product);

        $this->assertTrue($isLowStock);
        $product->refresh();
        $this->assertLessThanOrEqual(10, $product->stock);
    }

    /** @test */
    public function it_resets_low_stock_alert_when_restocked()
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

        // Restock above threshold
        $this->inventoryService->recordRestock($product, 10, 'Restock', $seller);

        $isLowStock = $this->inventoryService->checkLowStock($product);

        $this->assertFalse($isLowStock);
        $product->refresh();
        $this->assertGreaterThan(10, $product->stock);
    }
}

