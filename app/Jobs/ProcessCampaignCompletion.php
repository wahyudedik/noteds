<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Services\CampaignService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCampaignCompletion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Campaign $campaign
    ) {}

    public function handle(CampaignService $campaignService): void
    {
        try {
            if ($this->campaign->status === 'active' && $this->campaign->ended_at <= now()) {
                $campaignService->completeCampaign($this->campaign);
            }
        } catch (\Exception $e) {
            Log::error('ProcessCampaignCompletion failed: ' . $e->getMessage(), [
                'campaign_id' => $this->campaign->id,
                'exception' => $e,
            ]);
            throw $e;
        }
    }
}
