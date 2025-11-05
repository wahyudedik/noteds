<?php

namespace App\Console\Commands;

use App\Models\FeaturedNote;
use Illuminate\Console\Command;

class ExpireFeaturedNotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'featured:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire featured notes that have passed their end date.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired featured notes...');

        $expiredCount = FeaturedNote::where('status', 'active')
            ->where('end_date', '<', now())
            ->update(['status' => 'expired']);

        $this->info("Expired {$expiredCount} featured note(s).");

        return 0;
    }
}
