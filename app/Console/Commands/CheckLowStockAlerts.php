<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\InventoryManagementService;
use Illuminate\Console\Command;

class CheckLowStockAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-low-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all products for low stock and send alerts';

    /**
     * Execute the console command.
     */
    public function handle(InventoryManagementService $inventoryService): int
    {
        $this->info('Checking for low stock products...');

        try {
            $products = Product::whereNotNull('stock')
                ->where('stock', '>', 0)
                ->with('seller')
                ->get();

            $lowStockCount = 0;
            $bar = $this->output->createProgressBar($products->count());
            $bar->start();

            foreach ($products as $product) {
                if ($inventoryService->checkLowStock($product)) {
                    // Only send alert if not sent recently
                    $cooldownHours = config('seller.inventory.alert_cooldown_hours', 24);
                    $shouldSend = !$product->stock_alert_sent_at || 
                        $product->stock_alert_sent_at->lt(now()->subHours($cooldownHours));

                    if ($shouldSend) {
                        try {
                            $inventoryService->sendLowStockAlert($product);
                            $lowStockCount++;
                        } catch (\Exception $e) {
                            $this->warn("Failed to send alert for product {$product->id}: " . $e->getMessage());
                        }
                    }
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("Low stock alerts sent for {$lowStockCount} products.");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to check low stock: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('Failed to check low stock', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
