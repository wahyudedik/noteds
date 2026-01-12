<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Services\CampaignService;
use Illuminate\Console\Command;

class ActivateScheduledCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaigns:activate-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate campaigns that are scheduled to start';

    /**
     * Execute the console command.
     */
    public function handle(CampaignService $campaignService): int
    {
        $campaigns = Campaign::where('status', 'draft')
            ->whereNotNull('scheduled_start_at')
            ->where('scheduled_start_at', '<=', now())
            ->get();

        if ($campaigns->isEmpty()) {
            $this->info('No scheduled campaigns to activate.');
            return Command::SUCCESS;
        }

        $activated = 0;
        $failed = 0;

        foreach ($campaigns as $campaign) {
            try {
                if ($campaignService->activateCampaign($campaign)) {
                    $this->info("Activated campaign: {$campaign->title} (ID: {$campaign->id})");
                    $activated++;
                } else {
                    $this->warn("Failed to activate campaign: {$campaign->title} (ID: {$campaign->id})");
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->error("Error activating campaign {$campaign->id}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("Activated {$activated} campaign(s). Failed: {$failed}");

        return Command::SUCCESS;
    }
}
