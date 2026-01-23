<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $sellers = User::inRandomOrder()->limit(5)->get();
        foreach ($sellers as $seller) {
            foreach (range(1, rand(3, 6)) as $i) {
                $name = "Product {$seller->id}-{$i}";
                $product = Product::firstOrCreate(
                    ['user_id' => $seller->id, 'name' => $name],
                    [
                        'slug' => \Illuminate\Support\Str::slug($name) . '-' . substr((string) \Illuminate\Support\Str::uuid(), 0, 8),
                        'description' => 'Deskripsi produk contoh untuk pengujian.',
                        'price' => rand(10000, 250000) / 100.0,
                        'category' => $i % 2 === 0 ? 'Software' : 'Service',
                        'is_active' => true,
                        'stock' => rand(10, 100),
                        'sales_count' => 0,
                        'views_count' => rand(0, 500),
                    ]
                );

                // Simulate orders for some products
                foreach (range(1, rand(1, 4)) as $o) {
                    $buyer = User::inRandomOrder()->first();
                    if (!$buyer) continue;
                    $qty = rand(1, 3);
                    $order = Order::create([
                        'order_number' => Order::generateOrderNumber(),
                        'user_id' => $buyer->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'price' => $product->price,
                        'total' => $qty * (float) $product->price,
                        'status' => 'completed',
                        'payment_status' => 'paid',
                    ]);
                    $product->increment('sales_count', $qty);
                    if ($product->stock !== null) {
                        $product->decrement('stock', min($qty, $product->stock));
                    }
                }
            }
        }
    }
}
