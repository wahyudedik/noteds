<?php

namespace App\Console\Commands;

use App\Services\PointsService;
use Illuminate\Console\Command;

class ProcessExpiredPoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'points:process-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process expired points and update statistics.';

    /**
     * Execute the console command.
     */
    public function handle(PointsService $pointsService)
    {
        $this->info('Processing expired points...');

        try {
            $expiredCount = $pointsService->processExpiredPoints();
            $this->info("Found {$expiredCount} expired points.");
        } catch (\Exception $e) {
            $this->error("Error processing expired points: " . $e->getMessage());
            logger()->error("Error processing expired points: " . $e->getMessage(), ['exception' => $e]);
        }
    }
}
