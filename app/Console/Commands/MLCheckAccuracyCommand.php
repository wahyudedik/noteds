<?php

namespace App\Console\Commands;

use App\Jobs\CheckPredictionAccuracyJob;
use App\Models\Stock;
use Illuminate\Console\Command;

class MLCheckAccuracyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ml:check-accuracy 
                            {--stock= : Check accuracy for specific stock code}
                            {--all : Check accuracy for all active stocks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compare predictions with actual prices and update accuracy metrics';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $stockCode = $this->option('stock');
        $all = $this->option('all');

        if ($stockCode) {
            $this->info("Checking prediction accuracy for stock: {$stockCode}");
            CheckPredictionAccuracyJob::dispatch($stockCode);
            $this->info("Accuracy check job dispatched for {$stockCode}");
        } elseif ($all) {
            $count = Stock::active()->count();
            $this->info("Checking prediction accuracy for all {$count} active stocks...");
            
            $stocks = Stock::active()->get();
            $bar = $this->output->createProgressBar($count);
            $bar->start();

            foreach ($stocks as $stock) {
                CheckPredictionAccuracyJob::dispatch($stock->code);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("Dispatched accuracy check jobs for {$count} stocks");
        } else {
            $this->error('Please specify --stock=CODE or --all');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

