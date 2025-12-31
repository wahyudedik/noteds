<?php

namespace App\Jobs;

use App\Services\AutoTransferService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoTransferRewards implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(AutoTransferService $autoTransferService): void
    {
        try {
            $processed = $autoTransferService->processApprovedClips();
            
            Log::info("AutoTransferRewards processed {$processed} clips");
        } catch (\Exception $e) {
            Log::error('AutoTransferRewards failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            throw $e;
        }
    }
}
