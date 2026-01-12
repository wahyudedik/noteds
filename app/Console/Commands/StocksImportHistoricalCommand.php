<?php

namespace App\Console\Commands;

use App\Jobs\ImportHistoricalDataJob;
use App\Models\Stock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class StocksImportHistoricalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stocks:import-historical 
                            {--stock= : Stock code to import (e.g., BBRI)}
                            {--years=10 : Number of years of historical data to import}
                            {--all : Import for all active stocks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import historical stock data (10 years by default)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $stockCode = $this->option('stock');
        $years = (int) $this->option('years');
        $all = $this->option('all');

        if (!$stockCode && !$all) {
            $this->error('Please specify --stock=CODE or use --all to import for all stocks');
            return Command::FAILURE;
        }

        if ($stockCode && $all) {
            $this->error('Cannot use both --stock and --all options');
            return Command::FAILURE;
        }

        if ($years < 1 || $years > 20) {
            $this->error('Years must be between 1 and 20');
            return Command::FAILURE;
        }

        if ($all) {
            $stocks = Stock::active()->get();
            
            if ($stocks->isEmpty()) {
                $this->warn('No active stocks found');
                return Command::SUCCESS;
            }

            $this->info("Importing {$years} years of historical data for {$stocks->count()} stocks...");
            
            $bar = $this->output->createProgressBar($stocks->count());
            $bar->start();

            foreach ($stocks as $stock) {
                ImportHistoricalDataJob::dispatch($stock->code, $years);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("Dispatched {$stocks->count()} import jobs to queue");
        } else {
            $stock = Stock::where('code', $stockCode)->first();

            if (!$stock) {
                $this->error("Stock with code '{$stockCode}' not found");
                return Command::FAILURE;
            }

            $this->info("Importing {$years} years of historical data for {$stock->code}...");
            
            ImportHistoricalDataJob::dispatch($stock->code, $years);
            
            $this->info("Import job dispatched to queue");
        }

        return Command::SUCCESS;
    }
}

