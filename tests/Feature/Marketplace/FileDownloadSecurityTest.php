<?php

namespace Tests\Feature\Marketplace;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Download;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileDownloadSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $buyer;
    protected User $seller;
    protected User $otherUser;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('products');
        
        $this->buyer = User::factory()->create();
        $this->seller = User::factory()->create();
        $this->otherUser = User::factory()->create();
        
        $this->product = Product::create([
            'user_id' => $this->seller->id,
            'name' => 'Test Digital Product',
            'slug' => 'test-digital-product',
            'description' => 'Test product description',
            'price' => 100000,
            'category' => 'Software',
            'is_active' => true,
            'file_download' => 'test-file.zip',
        ]);

        // Create a test file
        Storage::disk('products')->put('test-file.zip', 'test file content');
    }

    /** @test */
    public function buyer_can_download_product_after_payment()
    {
        $order = Order::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price,
            'total' => $this->product->price,
            'status' => 'completed',
            'payment_status' => 'paid',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000001',
        ]);

        $this->actingAs($this->buyer);

        $response = $this->get(route('marketplace.products.download', $this->product));

        $response->assertStatus(200);
        $response->assertDownload('test-file.zip');
    }

    /** @test */
    public function user_cannot_download_without_purchase()
    {
        $this->actingAs($this->otherUser);

        $response = $this->get(route('marketplace.products.download', $this->product));

        $response->assertStatus(403);
        $response->assertSee('You must purchase this product to download it');
    }

    /** @test */
    public function user_cannot_download_with_unpaid_order()
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

        $this->actingAs($this->buyer);

        $response = $this->get(route('marketplace.products.download', $this->product));

        $response->assertStatus(403);
    }

    /** @test */
    public function user_cannot_download_other_users_purchased_product()
    {
        // Buyer purchases product
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

        // Other user tries to download
        $this->actingAs($this->otherUser);

        $response = $this->get(route('marketplace.products.download', $this->product));

        $response->assertStatus(403);
    }

    /** @test */
    public function download_creates_download_record()
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

        $this->actingAs($this->buyer);

        $this->get(route('marketplace.products.download', $this->product));

        $this->assertDatabaseHas('downloads', [
            'user_id' => $this->buyer->id,
            'order_id' => $order->id,
            'product_id' => $this->product->id,
        ]);
    }

    /** @test */
    public function download_fails_if_file_not_exists()
    {
        $product = Product::create([
            'user_id' => $this->seller->id,
            'name' => 'Product Without File',
            'slug' => 'product-without-file',
            'description' => 'Test product',
            'price' => 100000,
            'category' => 'Software',
            'is_active' => true,
            'file_download' => 'non-existent-file.zip',
        ]);

        $order = Order::create([
            'user_id' => $this->buyer->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
            'total' => $product->price,
            'status' => 'completed',
            'payment_status' => 'paid',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000005',
        ]);

        $this->actingAs($this->buyer);

        $response = $this->get(route('marketplace.products.download', $product));

        $response->assertStatus(404);
    }

    /** @test */
    public function download_fails_if_product_has_no_file()
    {
        $product = Product::create([
            'user_id' => $this->seller->id,
            'name' => 'Product Without File',
            'slug' => 'product-without-file-2',
            'description' => 'Test product',
            'price' => 100000,
            'category' => 'Software',
            'is_active' => true,
            'file_download' => null,
        ]);

        $order = Order::create([
            'user_id' => $this->buyer->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
            'total' => $product->price,
            'status' => 'completed',
            'payment_status' => 'paid',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000006',
        ]);

        $this->actingAs($this->buyer);

        $response = $this->get(route('marketplace.products.download', $product));

        $response->assertStatus(404);
        $response->assertSee('Download file not available');
    }

    /** @test */
    public function guest_cannot_download_product()
    {
        $order = Order::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price,
            'total' => $this->product->price,
            'status' => 'completed',
            'payment_status' => 'paid',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000007',
        ]);

        $response = $this->get(route('marketplace.products.download', $this->product));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function seller_cannot_download_own_product_without_purchase()
    {
        $this->actingAs($this->seller);

        $response = $this->get(route('marketplace.products.download', $this->product));

        $response->assertStatus(403);
    }

    /** @test */
    public function buyer_can_download_multiple_times()
    {
        $order = Order::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price,
            'total' => $this->product->price,
            'status' => 'completed',
            'payment_status' => 'paid',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000008',
        ]);

        $this->actingAs($this->buyer);

        // First download
        $response1 = $this->get(route('marketplace.products.download', $this->product));
        $response1->assertStatus(200);

        // Second download
        $response2 = $this->get(route('marketplace.products.download', $this->product));
        $response2->assertStatus(200);

        // Should have 2 download records
        $this->assertEquals(2, Download::where('user_id', $this->buyer->id)
            ->where('product_id', $this->product->id)
            ->count());
    }
}

