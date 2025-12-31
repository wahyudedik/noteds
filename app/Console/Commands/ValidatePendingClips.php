<?php

namespace App\Console\Commands;

use App\Models\Clip;
use App\Jobs\ValidateClipViews;
use Illuminate\Console\Command;

class ValidatePendingClips extends Command
{
    protected $signature = 'clipper:validate-pending-clips';

    protected $description = 'Validate views for pending clips after delay period';

    public function handle()
    {
        $delayHours = config('clipper.view_validation_delay_hours', 24);
        $cutoffTime = now()->subHours($delayHours);

        $clips = Clip::where('status', 'pending')
            ->where('submitted_at', '<=', $cutoffTime)
            ->get();

        $this->info("Validating {$clips->count()} pending clips...");

        foreach ($clips as $clip) {
            ValidateClipViews::dispatch($clip);
        }

        $this->info('Validation jobs dispatched successfully.');
    }
}
