<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\WaitlistService;
use Illuminate\Console\Command;

class CheckWaitlistStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waitlist:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check products with waitlist enabled and notify users when stock is available';

    protected WaitlistService $waitlistService;

    public function __construct(WaitlistService $waitlistService)
    {
        parent::__construct();
        $this->waitlistService = $waitlistService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking waitlist stock...');

        $products = Product::withWaitlist()
            ->whereNotNull('waitlist_notify_at_stock')
            ->get();

        $notifiedCount = 0;

        foreach ($products as $product) {
            try {
                $this->waitlistService->checkAndNotify($product);
                $notifiedCount++;
                $this->info("Checked product: {$product->name}");
            } catch (\Exception $e) {
                $this->error("Failed to check product {$product->id}: " . $e->getMessage());
            }
        }

        $this->info("Checked {$products->count()} products. Notifications sent for available stock.");

        return Command::SUCCESS;
    }
}
