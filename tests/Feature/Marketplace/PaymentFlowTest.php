<?php

namespace Tests\Feature\Marketplace;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\MidtransService;
use App\Services\MarketplaceService;
use App\Services\BalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $buyer;
    protected User $seller;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->buyer = User::factory()->create(['balance' => 0]);
        $this->seller = User::factory()->create(['balance' => 0]);
        $this->product = Product::create([
            'user_id' => $this->seller->id,
            'name' => 'Test Digital Product',
            'slug' => 'test-digital-product',
            'description' => 'Test product description',
            'price' => 100000,
            'category' => 'Software',
            'is_active' => true,
            'stock' => null,
            'sales_count' => 0,
            'views_count' => 0,
        ]);
    }

    /** @test */
    public function it_can_create_order_and_initiate_payment()
    {
        $this->actingAs($this->buyer);

        $response = $this->post(route('marketplace.orders.store'), [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
    }

    /** @test */
    public function it_processes_midtrans_webhook_for_settlement()
    {
        Queue::fake();
        
        $order = Order::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price,
            'total' => $this->product->price,
            'status' => 'pending',
            'payment_status' => 'pending',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000001',
        ]);

        // Mock Midtrans service to return successful verification
        $midtransService = Mockery::mock(MidtransService::class);
        $midtransService->shouldReceive('verifyWebhookSignature')
            ->andReturn(true);
        $midtransService->shouldReceive('handleWebhook')
            ->andReturn(true);
        
        $this->app->instance(MidtransService::class, $midtransService);

        $webhookData = [
            'order_id' => $order->order_number,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'transaction_id' => 'test-transaction-id',
        ];

        $response = $this->postJson(route('payment.webhook'), $webhookData);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('completed', $order->status);
        $this->assertNotNull($order->license_key);
    }

    /** @test */
    public function it_adds_balance_to_seller_after_payment()
    {
        $order = Order::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price,
            'total' => $this->product->price,
            'status' => 'pending',
            'payment_status' => 'pending',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000002',
        ]);

        $marketplaceService = app(MarketplaceService::class);
        $balanceService = app(BalanceService::class);

        // Simulate payment completion
        $order->markAsPaid();
        $marketplaceService->completeOrder($order);

        // Add balance to seller
        $balanceService->addBalance(
            $this->seller,
            $order->total,
            "Sale: Order #{$order->order_number}",
            $order->id,
            'sale'
        );

        $this->seller->refresh();
        $this->assertEquals(100000, $this->seller->balance);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->seller->id,
            'type' => 'sale',
            'amount' => 100000,
            'status' => 'completed',
            'reference_id' => $order->id,
        ]);
    }

    /** @test */
    public function it_handles_pending_payment_status()
    {
        $order = Order::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price,
            'total' => $this->product->price,
            'status' => 'pending',
            'payment_status' => 'pending',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000003',
        ]);

        $webhookData = [
            'order_id' => $order->order_number,
            'transaction_status' => 'pending',
            'transaction_id' => 'test-transaction-id',
        ];

        $midtransService = app(MidtransService::class);
        $result = $midtransService->handleWebhook($webhookData);

        $order->refresh();
        $this->assertEquals('pending', $order->payment_status);
    }

    /** @test */
    public function it_handles_failed_payment_status()
    {
        $order = Order::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price,
            'total' => $this->product->price,
            'status' => 'pending',
            'payment_status' => 'pending',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000004',
        ]);

        $webhookData = [
            'order_id' => $order->order_number,
            'transaction_status' => 'deny',
            'transaction_id' => 'test-transaction-id',
        ];

        $midtransService = app(MidtransService::class);
        $result = $midtransService->handleWebhook($webhookData);

        $order->refresh();
        $this->assertEquals('failed', $order->payment_status);
    }

    /** @test */
    public function it_updates_product_sales_count_after_payment()
    {
        $order = Order::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price,
            'total' => $this->product->price,
            'status' => 'pending',
            'payment_status' => 'paid',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000005',
        ]);

        $marketplaceService = app(MarketplaceService::class);
        $marketplaceService->completeOrder($order);

        $this->product->refresh();
        $this->assertEquals(1, $this->product->sales_count);
    }

    /** @test */
    public function it_generates_license_key_for_digital_product()
    {
        $order = Order::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price,
            'total' => $this->product->price,
            'status' => 'pending',
            'payment_status' => 'paid',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000006',
        ]);

        $marketplaceService = app(MarketplaceService::class);
        $marketplaceService->completeOrder($order);

        $order->refresh();
        $this->assertNotNull($order->license_key);
        $this->assertStringStartsWith('TES', $order->license_key);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

