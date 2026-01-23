<?php

namespace App\Console\Commands;

use App\Services\StreamScheduler;
use Illuminate\Console\Command;

class StreamScheduleRun extends Command
{
    protected $signature = 'streams:schedule:run';
    protected $description = 'Sync streams start/end based on events schedule';

    public function handle(StreamScheduler $scheduler)
    {
        $scheduler->sync();
        $this->info('Streams schedule sync completed');
        return Command::SUCCESS;
    }
}
