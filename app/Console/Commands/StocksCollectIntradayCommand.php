<?php

namespace App\Console\Commands;

use App\Jobs\CollectIntradayPricesJob;
use Illuminate\Console\Command;

class StocksCollectIntradayCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stocks:collect-intraday';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect real-time intraday prices for all active stocks (runs every minute during market hours)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Collecting intraday prices...');
        
        CollectIntradayPricesJob::dispatch();
        
        $this->info('Intraday collection job dispatched to queue');

        return Command::SUCCESS;
    }
}

