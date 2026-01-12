<?php

namespace App\Console\Commands;

use App\Services\DynamicPricingService;
use Illuminate\Console\Command;

class ProcessDynamicPricing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pricing:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled dynamic pricing rules';

    /**
     * Execute the console command.
     */
    public function handle(DynamicPricingService $pricingService): int
    {
        $this->info('Processing dynamic pricing rules...');

        try {
            $pricingService->processScheduledPricing();
            $this->info('Dynamic pricing rules processed successfully.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to process dynamic pricing: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('Failed to process dynamic pricing', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
