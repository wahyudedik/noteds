<?php

namespace App\Console\Commands;

use App\Models\FeaturedNote;
use Illuminate\Console\Command;

class ActivateScheduledFeaturedNotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'featured:activate-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate scheduled featured notes that have reached their scheduled date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->startOfDay();
        
        $scheduledNotes = FeaturedNote::where('status', 'active')
            ->whereNotNull('scheduled_date')
            ->where('scheduled_date', '<=', $today)
            ->whereNull('start_date')
            ->get();

        $activated = 0;

        foreach ($scheduledNotes as $featuredNote) {
            $featuredNote->update([
                'start_date' => $featuredNote->scheduled_date,
                'end_date' => $featuredNote->scheduled_date->copy()->addDays($featuredNote->duration_days),
            ]);
            $activated++;
        }

        if ($activated > 0) {
            $this->info("Activated {$activated} scheduled featured note(s).");
        } else {
            $this->info('No scheduled featured notes to activate.');
        }

        return Command::SUCCESS;
    }
}
