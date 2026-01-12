<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SellerRatingService;
use Illuminate\Console\Command;

class RecalculateSellerMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seller-metrics:recalculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate seller performance metrics and ratings';

    /**
     * Execute the console command.
     */
    public function handle(SellerRatingService $ratingService): int
    {
        $this->info('Recalculating seller metrics...');

        try {
            $sellers = User::whereHas('products')->get();
            $bar = $this->output->createProgressBar($sellers->count());
            $bar->start();

            foreach ($sellers as $seller) {
                try {
                    $ratingService->recalculatePerformanceMetrics($seller);
                    $ratingService->updateSellerRating($seller);
                } catch (\Exception $e) {
                    $this->warn("Failed to recalculate metrics for seller {$seller->id}: " . $e->getMessage());
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info('Seller metrics recalculated successfully.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to recalculate seller metrics: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('Failed to recalculate seller metrics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
