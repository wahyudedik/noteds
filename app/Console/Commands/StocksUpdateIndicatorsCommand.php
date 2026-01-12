<?php

namespace App\Console\Commands;

use App\Jobs\UpdateTechnicalIndicatorsJob;
use App\Models\Stock;
use Illuminate\Console\Command;

class StocksUpdateIndicatorsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stocks:update-indicators 
                            {--stock= : Update indicators for specific stock code}
                            {--all : Update indicators for all active stocks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate technical indicators for all stocks or specific stock';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $stockCode = $this->option('stock');
        $all = $this->option('all');

        if ($stockCode) {
            $this->info("Updating technical indicators for stock: {$stockCode}");
            UpdateTechnicalIndicatorsJob::dispatch($stockCode);
            $this->info("Indicator update job dispatched for {$stockCode}");
        } elseif ($all) {
            $count = Stock::active()->count();
            $this->info("Updating technical indicators for all {$count} active stocks...");
            
            $stocks = Stock::active()->get();
            $bar = $this->output->createProgressBar($count);
            $bar->start();

            foreach ($stocks as $stock) {
                UpdateTechnicalIndicatorsJob::dispatch($stock->code);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("Dispatched indicator update jobs for {$count} stocks");
        } else {
            $this->error('Please specify --stock=CODE or --all');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

