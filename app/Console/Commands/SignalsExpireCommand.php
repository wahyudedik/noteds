<?php

namespace App\Console\Commands;

use App\Services\StockSignalService;
use Illuminate\Console\Command;

class SignalsExpireCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'signals:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark expired signals as inactive';

    /**
     * Execute the console command.
     */
    public function handle(StockSignalService $signalService): int
    {
        $this->info('Expiring old signals...');
        
        $expiredCount = $signalService->expireOldSignals();
        
        $this->info("Expired {$expiredCount} signals");
        
        return Command::SUCCESS;
    }
}

