<?php

namespace App\Console\Commands;

use App\Jobs\AutoTransferRewards;
use Illuminate\Console\Command;

class AutoTransferPendingRewards extends Command
{
    protected $signature = 'clipper:auto-transfer-rewards';

    protected $description = 'Auto transfer rewards for approved clips';

    public function handle()
    {
        $this->info('Processing auto transfer rewards...');

        AutoTransferRewards::dispatch();

        $this->info('Auto transfer job dispatched successfully.');
    }
}
