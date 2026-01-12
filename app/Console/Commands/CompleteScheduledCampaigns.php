<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Services\CampaignService;
use Illuminate\Console\Command;

class CompleteScheduledCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaigns:complete-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Complete campaigns that are scheduled to end';

    /**
     * Execute the console command.
     */
    public function handle(CampaignService $campaignService): int
    {
        $campaigns = Campaign::whereIn('status', ['active', 'paused'])
            ->whereNotNull('scheduled_end_at')
            ->where('scheduled_end_at', '<=', now())
            ->get();

        if ($campaigns->isEmpty()) {
            $this->info('No scheduled campaigns to complete.');
            return Command::SUCCESS;
        }

        $completed = 0;
        $failed = 0;

        foreach ($campaigns as $campaign) {
            try {
                if ($campaignService->completeCampaign($campaign)) {
                    $this->info("Completed campaign: {$campaign->title} (ID: {$campaign->id})");
                    $completed++;
                } else {
                    $this->warn("Failed to complete campaign: {$campaign->title} (ID: {$campaign->id})");
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->error("Error completing campaign {$campaign->id}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("Completed {$completed} campaign(s). Failed: {$failed}");

        return Command::SUCCESS;
    }
}
