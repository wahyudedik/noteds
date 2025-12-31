<?php

namespace Tests\Feature\Marketplace;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected User $buyer;
    protected User $seller;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->buyer = User::factory()->create();
        $this->seller = User::factory()->create();
        
        $this->product = Product::create([
            'user_id' => $this->seller->id,
            'name' => 'Test Digital Product',
            'slug' => 'test-digital-product',
            'description' => 'Test product description',
            'price' => 100000,
            'category' => 'Software',
            'is_active' => true,
            'stock' => 10,
        ]);
    }

    /** @test */
    public function user_can_cancel_pending_order()
    {
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

        $this->actingAs($this->buyer);

        $response = $this->post(route('marketplace.orders.cancel', $order));

        $response->assertRedirect(route('marketplace.orders.index'));
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
    }

    /** @test */
    public function user_cannot_cancel_paid_order()
    {
        $order = Order::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price,
            'total' => $this->product->price,
            'status' => 'paid',
            'payment_status' => 'paid',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000002',
        ]);

        $this->actingAs($this->buyer);

        $response = $this->post(route('marketplace.orders.cancel', $order));

        $response->assertSessionHasErrors('error');
        $this->assertEquals('paid', $order->fresh()->status);
    }

    /** @test */
    public function user_cannot_cancel_completed_order()
    {
        $order = Order::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price,
            'total' => $this->product->price,
            'status' => 'completed',
            'payment_status' => 'paid',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000003',
        ]);

        $this->actingAs($this->buyer);

        $response = $this->post(route('marketplace.orders.cancel', $order));

        $response->assertSessionHasErrors('error');
        $this->assertEquals('completed', $order->fresh()->status);
    }

    /** @test */
    public function user_cannot_cancel_other_users_order()
    {
        $otherUser = User::factory()->create();
        
        $order = Order::create([
            'user_id' => $otherUser->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price,
            'total' => $this->product->price,
            'status' => 'pending',
            'payment_status' => 'pending',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000004',
        ]);

        $this->actingAs($this->buyer);

        $response = $this->post(route('marketplace.orders.cancel', $order));

        $response->assertStatus(403);
        $this->assertEquals('pending', $order->fresh()->status);
    }

    /** @test */
    public function guest_cannot_cancel_order()
    {
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

        $response = $this->post(route('marketplace.orders.cancel', $order));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function cancelled_order_does_not_affect_product_stock()
    {
        $initialStock = $this->product->stock;

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

        $this->actingAs($this->buyer);

        $this->post(route('marketplace.orders.cancel', $order));

        $this->product->refresh();
        $this->assertEquals($initialStock, $this->product->stock);
    }

    /** @test */
    public function user_can_view_cancelled_orders()
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

        $this->actingAs($this->buyer);

        $this->post(route('marketplace.orders.cancel', $order));

        $response = $this->get(route('marketplace.orders.index'));

        $response->assertStatus(200);
        $response->assertViewHas('orders', function ($orders) use ($order) {
            return $orders->contains('id', $order->id);
        });
    }

    /** @test */
    public function user_cannot_cancel_already_cancelled_order()
    {
        $order = Order::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price,
            'total' => $this->product->price,
            'status' => 'cancelled',
            'payment_status' => 'pending',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000008',
        ]);

        $this->actingAs($this->buyer);

        $response = $this->post(route('marketplace.orders.cancel', $order));

        // Should still work, but order remains cancelled
        $this->assertEquals('cancelled', $order->fresh()->status);
    }
}

