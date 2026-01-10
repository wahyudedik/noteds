<?php

namespace Tests\Feature\Marketplace;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Jobs\ProcessMidtransWebhook;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class WebhookRetryTest extends TestCase
{
    use RefreshDatabase;

    protected User $buyer;
    protected User $seller;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->buyer = User::factory()->create();
        $this->seller = User::factory()->create(['balance' => 0]);
        
        $this->product = Product::create([
            'user_id' => $this->seller->id,
            'name' => 'Test Digital Product',
            'slug' => 'test-digital-product',
            'description' => 'Test product description',
            'price' => 100000,
            'category' => 'Software',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function webhook_job_is_dispatched_to_queue()
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

        $webhookData = [
            'order_id' => $order->order_number,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'transaction_id' => 'test-transaction-id',
        ];

        ProcessMidtransWebhook::dispatch($webhookData);

        Queue::assertPushed(ProcessMidtransWebhook::class);
    }

    /** @test */
    public function webhook_job_retries_on_failure()
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

        $webhookData = [
            'order_id' => $order->order_number,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'transaction_id' => 'test-transaction-id',
        ];

        // Mock MidtransService to throw exception
        $midtransService = Mockery::mock(MidtransService::class);
        $midtransService->shouldReceive('handleWebhook')
            ->andThrow(new \Exception('Webhook processing failed'));

        $this->app->instance(MidtransService::class, $midtransService);

        $job = new ProcessMidtransWebhook($webhookData);
        
        $this->expectException(\Exception::class);
        $job->handle(
            $midtransService,
            app(\App\Services\MarketplaceService::class),
            app(\App\Services\BalanceService::class),
            app(\App\Services\NotificationService::class)
        );
    }

    /** @test */
    public function webhook_handles_missing_order_gracefully()
    {
        // Mock MidtransService to return true for handleWebhook (signature check passes)
        // but order will not be found in the database
        $midtransService = Mockery::mock(MidtransService::class);
        $midtransService->shouldReceive('handleWebhook')
            ->andReturn(true); // Signature verification passes
        
        $this->app->instance(MidtransService::class, $midtransService);

        Log::shouldReceive('warning')
            ->once()
            ->with(Mockery::pattern('/Midtrans webhook: Order not found for order_id/'), Mockery::type('array'));

        $webhookData = [
            'order_id' => 'ORD-INVALID',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'transaction_id' => 'test-transaction-id',
        ];

        $response = $this->postJson(route('payment.webhook'), $webhookData);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'error', 'message' => 'Order not found']);
    }

    /** @test */
    public function webhook_handles_invalid_signature()
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

        // Mock MidtransService to return false for signature verification
        $midtransService = Mockery::mock(MidtransService::class);
        $midtransService->shouldReceive('verifyWebhookSignature')
            ->andReturn(false);
        $midtransService->shouldReceive('handleWebhook')
            ->andReturn(false);

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

        // Order should not be updated
        $order->refresh();
        $this->assertEquals('pending', $order->payment_status);
    }

    /** @test */
    public function webhook_processes_duplicate_notifications()
    {
        $order = Order::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price,
            'total' => $this->product->price,
            'status' => 'completed',
            'payment_status' => 'paid',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000004',
        ]);

        $webhookData = [
            'order_id' => $order->order_number,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'transaction_id' => 'test-transaction-id',
        ];

        // First webhook
        $response1 = $this->postJson(route('payment.webhook'), $webhookData);
        $response1->assertStatus(200);

        // Duplicate webhook (should be idempotent)
        $response2 = $this->postJson(route('payment.webhook'), $webhookData);
        $response2->assertStatus(200);

        // Order should remain in completed state
        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('completed', $order->status);
    }

    /** @test */
    public function webhook_logs_errors_on_failure()
    {
        Log::shouldReceive('error')
            ->once()
            ->with(Mockery::pattern('/ProcessMidtransWebhook failed/'), Mockery::type('array'));

        $order = Order::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price,
            'total' => $this->product->price,
            'status' => 'pending',
            'payment_status' => 'pending',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000005',
        ]);

        // Mock MidtransService to throw exception
        $midtransService = Mockery::mock(MidtransService::class);
        $midtransService->shouldReceive('handleWebhook')
            ->andThrow(new \Exception('Database connection failed'));

        $this->app->instance(MidtransService::class, $midtransService);

        $webhookData = [
            'order_id' => $order->order_number,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'transaction_id' => 'test-transaction-id',
        ];

        $job = new ProcessMidtransWebhook($webhookData);
        
        try {
            $job->handle(
                $midtransService,
                app(\App\Services\MarketplaceService::class),
                app(\App\Services\BalanceService::class),
                app(\App\Services\NotificationService::class)
            );
        } catch (\Exception $e) {
            // Expected to throw
        }
    }

    /** @test */
    public function webhook_handles_different_transaction_statuses()
    {
        $order = Order::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price,
            'total' => $this->product->price,
            'status' => 'pending',
            'payment_status' => 'pending',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000006',
        ]);

        $midtransService = app(MidtransService::class);

        // Test expire status
        $webhookData = [
            'order_id' => $order->order_number,
            'transaction_status' => 'expire',
            'transaction_id' => 'test-transaction-id',
        ];

        $result = $midtransService->handleWebhook($webhookData);
        $order->refresh();
        $this->assertEquals('failed', $order->payment_status);

        // Reset order
        $order->update(['payment_status' => 'pending']);

        // Test cancel status
        $webhookData['transaction_status'] = 'cancel';
        $result = $midtransService->handleWebhook($webhookData);
        $order->refresh();
        $this->assertEquals('failed', $order->payment_status);
    }

    /** @test */
    public function webhook_updates_transaction_id()
    {
        $order = Order::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price,
            'total' => $this->product->price,
            'status' => 'pending',
            'payment_status' => 'pending',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000007',
        ]);

        $webhookData = [
            'order_id' => $order->order_number,
            'transaction_status' => 'pending',
            'transaction_id' => 'midtrans-txn-12345',
        ];

        $midtransService = app(MidtransService::class);
        $midtransService->handleWebhook($webhookData);

        $order->refresh();
        $this->assertEquals('midtrans-txn-12345', $order->midtrans_transaction_id);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

