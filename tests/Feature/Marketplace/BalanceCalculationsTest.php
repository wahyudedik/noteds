<?php

namespace Tests\Feature\Marketplace;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Services\BalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BalanceCalculationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;
    protected BalanceService $balanceService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seller = User::factory()->create(['balance' => 0]);
        $this->balanceService = app(BalanceService::class);
    }

    /** @test */
    public function balance_is_added_correctly_after_sale()
    {
        $product = Product::create([
            'user_id' => $this->seller->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Test',
            'price' => 100000,
            'category' => 'Software',
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => User::factory()->create()->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100000,
            'total' => 100000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000001',
        ]);

        $this->balanceService->addBalance(
            $this->seller,
            $order->total,
            "Sale: Order #{$order->order_number}",
            $order->id,
            'sale'
        );

        $this->seller->refresh();
        $this->assertEquals(100000, $this->seller->balance);
    }

    /** @test */
    public function balance_is_deducted_correctly_for_withdrawal()
    {
        $this->seller->update(['balance' => 500000]);

        $withdrawal = Withdrawal::create([
            'user_id' => $this->seller->id,
            'amount' => 100000,
            'method' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'status' => 'approved',
            'user_type' => 'seller',
        ]);

        $this->balanceService->deductBalance(
            $this->seller,
            $withdrawal->amount,
            "Withdrawal #{$withdrawal->id}",
            $withdrawal->id
        );

        $this->seller->refresh();
        $this->assertEquals(400000, $this->seller->balance);
    }

    /** @test */
    public function multiple_sales_accumulate_balance_correctly()
    {
        $product = Product::create([
            'user_id' => $this->seller->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Test',
            'price' => 50000,
            'category' => 'Software',
            'is_active' => true,
        ]);

        $buyer = User::factory()->create();

        // First sale
        $order1 = Order::create([
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 50000,
            'total' => 50000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000001',
        ]);

        $this->balanceService->addBalance(
            $this->seller,
            $order1->total,
            "Sale: Order #{$order1->order_number}",
            $order1->id,
            'sale'
        );

        // Second sale
        $order2 = Order::create([
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 50000,
            'total' => 50000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000002',
        ]);

        $this->balanceService->addBalance(
            $this->seller,
            $order2->total,
            "Sale: Order #{$order2->order_number}",
            $order2->id,
            'sale'
        );

        $this->seller->refresh();
        $this->assertEquals(100000, $this->seller->balance);
    }

    /** @test */
    public function transaction_records_balance_before_and_after()
    {
        $this->seller->update(['balance' => 100000]);

        $transaction = $this->balanceService->addBalance(
            $this->seller,
            50000,
            'Test transaction',
            null,
            'sale'
        );

        $this->assertEquals(100000, $transaction->balance_before);
        $this->assertEquals(150000, $transaction->balance_after);
        $this->assertEquals(50000, $transaction->amount);
    }

    /** @test */
    public function cannot_deduct_more_than_balance()
    {
        $this->seller->update(['balance' => 50000]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient balance');

        $this->balanceService->deductBalance(
            $this->seller,
            100000,
            'Test withdrawal',
            null
        );
    }

    /** @test */
    public function balance_calculation_is_precise_with_decimals()
    {
        $product = Product::create([
            'user_id' => $this->seller->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Test',
            'price' => 99999.99,
            'category' => 'Software',
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => User::factory()->create()->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 99999.99,
            'total' => 99999.99,
            'status' => 'completed',
            'payment_status' => 'paid',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000001',
        ]);

        $this->balanceService->addBalance(
            $this->seller,
            $order->total,
            "Sale: Order #{$order->order_number}",
            $order->id,
            'sale'
        );

        $this->seller->refresh();
        $this->assertEquals(99999.99, $this->seller->balance);
    }

    /** @test */
    public function withdrawal_creates_transaction_record()
    {
        $this->seller->update(['balance' => 200000]);

        $withdrawal = Withdrawal::create([
            'user_id' => $this->seller->id,
            'amount' => 100000,
            'method' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'status' => 'approved',
            'user_type' => 'seller',
        ]);

        $transaction = $this->balanceService->deductBalance(
            $this->seller,
            $withdrawal->amount,
            "Withdrawal #{$withdrawal->id}",
            $withdrawal->id
        );

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->seller->id,
            'type' => 'withdrawal',
            'amount' => 100000,
            'balance_before' => 200000,
            'balance_after' => 100000,
            'status' => 'completed',
            'reference_id' => $withdrawal->id,
        ]);
    }

    /** @test */
    public function sale_creates_transaction_record()
    {
        $product = Product::create([
            'user_id' => $this->seller->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Test',
            'price' => 150000,
            'category' => 'Software',
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => User::factory()->create()->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 150000,
            'total' => 150000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000001',
        ]);

        $transaction = $this->balanceService->addBalance(
            $this->seller,
            $order->total,
            "Sale: Order #{$order->order_number}",
            $order->id,
            'sale'
        );

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->seller->id,
            'type' => 'sale',
            'amount' => 150000,
            'balance_before' => 0,
            'balance_after' => 150000,
            'status' => 'completed',
            'reference_id' => $order->id,
        ]);
    }

    /** @test */
    public function get_balance_returns_current_balance()
    {
        $this->seller->update(['balance' => 250000]);

        $balance = $this->balanceService->getBalance($this->seller);

        $this->assertEquals(250000, $balance);
    }

    /** @test */
    public function get_balance_history_returns_transactions()
    {
        $product = Product::create([
            'user_id' => $this->seller->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Test',
            'price' => 100000,
            'category' => 'Software',
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => User::factory()->create()->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100000,
            'total' => 100000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000001',
        ]);

        $this->balanceService->addBalance(
            $this->seller,
            $order->total,
            "Sale: Order #{$order->order_number}",
            $order->id,
            'sale'
        );

        $history = $this->balanceService->getBalanceHistory($this->seller);

        $this->assertCount(1, $history);
        $this->assertEquals('sale', $history->first()->type);
        $this->assertEquals(100000, $history->first()->amount);
    }

    /** @test */
    public function balance_calculation_with_multiple_operations()
    {
        $product = Product::create([
            'user_id' => $this->seller->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Test',
            'price' => 200000,
            'category' => 'Software',
            'is_active' => true,
        ]);

        $buyer = User::factory()->create();

        // Add balance from sale
        $order = Order::create([
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 200000,
            'total' => 200000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'order_number' => 'ORD-' . now()->format('Ymd') . '-000001',
        ]);

        $this->balanceService->addBalance(
            $this->seller,
            $order->total,
            "Sale: Order #{$order->order_number}",
            $order->id,
            'sale'
        );

        $this->seller->refresh();
        $this->assertEquals(200000, $this->seller->balance);

        // Deduct balance for withdrawal
        $withdrawal = Withdrawal::create([
            'user_id' => $this->seller->id,
            'amount' => 50000,
            'method' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
            'status' => 'approved',
            'user_type' => 'seller',
        ]);

        $this->balanceService->deductBalance(
            $this->seller,
            $withdrawal->amount,
            "Withdrawal #{$withdrawal->id}",
            $withdrawal->id
        );

        $this->seller->refresh();
        $this->assertEquals(150000, $this->seller->balance);
    }
}

