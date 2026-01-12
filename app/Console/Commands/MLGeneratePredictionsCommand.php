<?php

namespace App\Console\Commands;

use App\Jobs\GeneratePredictionsJob;
use App\Models\Stock;
use Illuminate\Console\Command;

class MLGeneratePredictionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ml:generate-predictions 
                            {--stock= : Generate predictions for specific stock code}
                            {--horizon=1 : Prediction horizon in days (1, 7, 30)}
                            {--all : Generate predictions for all active stocks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate predictions for all active stocks';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $stockCode = $this->option('stock');
        $all = $this->option('all');
        $horizon = (int) $this->option('horizon');

        if ($stockCode) {
            $this->info("Generating predictions for stock: {$stockCode} (horizon: {$horizon} days)");
            GeneratePredictionsJob::dispatch($stockCode, $horizon, null);
            $this->info("Prediction generation job dispatched for {$stockCode}");
        } elseif ($all) {
            $count = Stock::active()->count();
            $this->info("Generating predictions for all {$count} active stocks (horizon: {$horizon} days)...");
            
            $stocks = Stock::active()->get();
            $bar = $this->output->createProgressBar($count);
            $bar->start();

            foreach ($stocks as $stock) {
                GeneratePredictionsJob::dispatch($stock->code, $horizon, null);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("Dispatched prediction generation jobs for {$count} stocks");
        } else {
            $this->error('Please specify --stock=CODE or --all');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

