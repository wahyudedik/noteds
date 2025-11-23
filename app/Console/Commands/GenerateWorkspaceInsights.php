<?php

namespace App\Console\Commands;

use App\Services\WorkspaceInsightService;
use Illuminate\Console\Command;

class GenerateWorkspaceInsights extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workspaces:generate-insights';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate weekly digests and detect anomalies for all workspaces';

    /**
     * Execute the console command.
     */
    public function handle(WorkspaceInsightService $insightService): int
    {
        $this->info('Generating workspace insights...');

        $generated = $insightService->generateAllInsights();

        $this->info("Successfully generated insights for {$generated} workspace(s).");

        return Command::SUCCESS;
    }
}

