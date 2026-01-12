<?php

namespace Tests\Integration\Marketplace;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Services\DynamicPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PricingRulesWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected DynamicPricingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DynamicPricingService::class);
    }

    /** @test */
    public function it_can_create_and_apply_time_based_rule()
    {
        $seller = User::factory()->create();
        $product = Product::create([
            'user_id' => $seller->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => 100000,
            'base_price' => 100000,
            'pricing_rules_enabled' => true,
        ]);

        $rule = $this->service->createRule([
            'rule_name' => 'Flash Sale',
            'rule_type' => 'time_based',
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'start_time' => '00:00',
            'end_time' => '23:59',
            'adjustment_type' => 'percentage',
            'adjustment_value' => -20,
            'is_active' => true,
            'priority' => 10,
        ], $product);

        $this->assertNotNull($rule);
        $this->assertEquals('time_based', $rule->rule_type);

        $effectivePrice = $this->service->calculateEffectivePrice($product);
        $this->assertIsNumeric($effectivePrice);
    }

    /** @test */
    public function it_can_create_stock_based_rule()
    {
        $seller = User::factory()->create();
        $product = Product::create([
            'user_id' => $seller->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => 100000,
            'base_price' => 100000,
            'stock' => 5,
            'pricing_rules_enabled' => true,
        ]);

        $rule = $this->service->createRule([
            'rule_name' => 'Clearance Sale',
            'rule_type' => 'stock_based',
            'stock_threshold' => 10,
            'stock_condition' => 'below',
            'adjustment_type' => 'percentage',
            'adjustment_value' => -30,
            'is_active' => true,
            'priority' => 10,
        ], $product);

        $this->assertNotNull($rule);
        $this->assertEquals('stock_based', $rule->rule_type);
    }
}

