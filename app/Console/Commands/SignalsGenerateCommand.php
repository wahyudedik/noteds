<?php

namespace App\Console\Commands;

use App\Jobs\GenerateSignalsJob;
use App\Models\Stock;
use Illuminate\Console\Command;

class SignalsGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'signals:generate 
                            {--stock= : Generate signals for specific stock code}
                            {--all : Generate signals for all active stocks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate buy/sell signals based on current predictions and indicators';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $stockCode = $this->option('stock');
        $all = $this->option('all');

        if ($stockCode) {
            $this->info("Generating signals for stock: {$stockCode}");
            GenerateSignalsJob::dispatch($stockCode);
            $this->info("Signal generation job dispatched for {$stockCode}");
        } elseif ($all) {
            $count = Stock::active()->count();
            $this->info("Generating signals for all {$count} active stocks...");
            
            $stocks = Stock::active()->get();
            $bar = $this->output->createProgressBar($count);
            $bar->start();

            foreach ($stocks as $stock) {
                GenerateSignalsJob::dispatch($stock->code);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("Dispatched signal generation jobs for {$count} stocks");
        } else {
            $this->error('Please specify --stock=CODE or --all');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

