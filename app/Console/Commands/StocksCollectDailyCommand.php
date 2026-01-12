<?php

namespace App\Console\Commands;

use App\Jobs\CollectStockDataJob;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Console\Command;

class StocksCollectDailyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stocks:collect-daily 
                            {--date= : Date to collect prices for (YYYY-MM-DD, default: today)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect daily stock prices for all active stocks';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dateString = $this->option('date');
        
        if ($dateString) {
            try {
                $date = Carbon::parse($dateString);
            } catch (\Exception $e) {
                $this->error("Invalid date format. Use YYYY-MM-DD");
                return Command::FAILURE;
            }
        } else {
            $date = Carbon::today();
        }

        // Skip if weekend
        if ($date->isWeekend()) {
            $this->warn("Skipping collection for weekend: {$date->format('Y-m-d')}");
            return Command::SUCCESS;
        }

        $stocks = Stock::active()->get();

        if ($stocks->isEmpty()) {
            $this->warn('No active stocks found');
            return Command::SUCCESS;
        }

        $this->info("Collecting daily prices for {$stocks->count()} stocks on {$date->format('Y-m-d')}...");

        $bar = $this->output->createProgressBar($stocks->count());
        $bar->start();

        foreach ($stocks as $stock) {
            CollectStockDataJob::dispatch($stock->code, $date);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Dispatched {$stocks->count()} collection jobs to queue");

        return Command::SUCCESS;
    }
}

