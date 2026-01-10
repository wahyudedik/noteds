<?php

namespace App\Console\Commands;

use App\Services\PostAnalyticsService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class AggregatePostAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:aggregate-analytics {--date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate post analytics for a specific date (defaults to yesterday)';

    /**
     * Execute the console command.
     */
    public function handle(PostAnalyticsService $analyticsService): int
    {
        $dateOption = $this->option('date');
        $date = $dateOption 
            ? Carbon::parse($dateOption)
            : Carbon::yesterday();

        $this->info("Aggregating analytics for {$date->format('Y-m-d')}...");

        $processed = $analyticsService->aggregateForDate($date);

        $this->info("Processed {$processed} posts.");

        return Command::SUCCESS;
    }
}


