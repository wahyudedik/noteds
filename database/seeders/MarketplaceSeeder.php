<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarketplaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users (excluding admin)
        $users = User::where('role', '!=', 'admin')
            ->orWhereNull('role')
            ->get();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        $categories = ['Software', 'Template', 'Course', 'E-book', 'Design', 'Plugin'];
        
        // Create sample products
        $products = [];
        foreach ($users->take(5) as $index => $seller) {
            $category = $categories[array_rand($categories)];
            $productName = "Sample {$category} Product " . ($index + 1);
            
            $product = Product::create([
                'user_id' => $seller->id,
                'name' => $productName,
                'slug' => Str::slug($productName) . '-' . time() . '-' . $index,
                'description' => "This is a sample {$category} product. Perfect for testing the marketplace functionality. Includes full documentation and support.",
                'price' => rand(50000, 500000),
                'category' => $category,
                'image' => null,
                'file_download' => null,
                'license_key' => null,
                'is_active' => true,
                'stock' => rand(10, 100),
                'sales_count' => 0,
                'views_count' => rand(0, 50),
            ]);
            
            $products[] = $product;
            
            $this->command->info("Created product: {$product->name}");
        }

        // Create some sample orders (completed)
        if (count($products) > 0) {
            foreach (collect($products)->take(3) as $product) {
                $buyer = $users->random();
                
                // Skip if buyer is the seller
                while ($buyer->id === $product->user_id) {
                    $buyer = $users->random();
                }
                
                $quantity = 1;
                $order = Order::create([
                    'order_number' => Order::generateOrderNumber(), // Explicitly set order_number
                    'user_id' => $buyer->id, // buyer_id in migration
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'total' => $product->price * $quantity,
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'midtrans_order_id' => 'TEST-' . time() . '-' . $buyer->id,
                    'midtrans_transaction_id' => 'TEST-TXN-' . time() . '-' . $buyer->id,
                    'license_key' => 'TEST-' . strtoupper(Str::random(16)),
                ]);
                
                // Update product sales count
                $product->increment('sales_count');
                
                // Update seller balance
                $seller = $product->seller;
                if ($seller) {
                    $seller->increment('balance', $order->total);
                }
                
                $this->command->info("Created order: {$order->order_number} - {$buyer->name} bought {$product->name}");
            }
        }

        $this->command->info('Marketplace seeding completed!');
    }
}
