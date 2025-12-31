<?php

namespace App\Console\Commands;

use App\Models\Clip;
use App\Jobs\TrackClipViews;
use Illuminate\Console\Command;

class TrackAllClipViews extends Command
{
    protected $signature = 'clipper:track-views';

    protected $description = 'Track views for all active clips';

    public function handle()
    {
        $clips = Clip::where('status', 'pending')
            ->orWhere('status', 'approved')
            ->whereNull('paid_at')
            ->get();

        $this->info("Tracking views for {$clips->count()} clips...");

        foreach ($clips as $clip) {
            TrackClipViews::dispatch($clip);
        }

        $this->info('View tracking jobs dispatched successfully.');
    }
}
