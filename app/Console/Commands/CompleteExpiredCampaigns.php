<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Jobs\ProcessCampaignCompletion;
use Illuminate\Console\Command;

class CompleteExpiredCampaigns extends Command
{
    protected $signature = 'clipper:complete-expired-campaigns';

    protected $description = 'Complete expired campaigns and refund remaining budget';

    public function handle()
    {
        $campaigns = Campaign::where('status', 'active')
            ->where('ended_at', '<=', now())
            ->get();

        $this->info("Completing {$campaigns->count()} expired campaigns...");

        foreach ($campaigns as $campaign) {
            ProcessCampaignCompletion::dispatch($campaign);
        }

        $this->info('Campaign completion jobs dispatched successfully.');
    }
}
