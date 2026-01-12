<?php

namespace App\Console\Commands;

use App\Jobs\TrainMLModelJob;
use App\Models\Stock;
use Illuminate\Console\Command;

class MLRetrainModelsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ml:retrain-models 
                            {--stock= : Retrain models for specific stock code}
                            {--all : Retrain models for all active stocks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrain ML models for stocks';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $stockCode = $this->option('stock');
        $all = $this->option('all');
        $modelTypes = ['lstm', 'transformer', 'cnn_lstm'];
        $horizons = [1, 7, 30];

        if ($stockCode) {
            $this->info("Retraining models for stock: {$stockCode}");
            
            foreach ($modelTypes as $modelType) {
                foreach ($horizons as $horizon) {
                    TrainMLModelJob::dispatch($stockCode, $modelType, $horizon);
                }
            }
            
            $this->info("Model retraining jobs dispatched for {$stockCode}");
        } elseif ($all) {
            $count = Stock::active()->count();
            $this->info("Retraining models for all {$count} active stocks...");
            
            $stocks = Stock::active()->get();
            $bar = $this->output->createProgressBar($count);
            $bar->start();

            foreach ($stocks as $stock) {
                foreach ($modelTypes as $modelType) {
                    foreach ($horizons as $horizon) {
                        TrainMLModelJob::dispatch($stock->code, $modelType, $horizon);
                    }
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("Dispatched model retraining jobs for {$count} stocks");
        } else {
            $this->error('Please specify --stock=CODE or --all');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

