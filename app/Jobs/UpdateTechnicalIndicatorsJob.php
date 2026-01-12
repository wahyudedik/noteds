<?php

namespace App\Jobs;

use App\Services\TechnicalIndicatorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job to update technical indicators for a stock.
 * 
 * Queue: indicators
 */
class UpdateTechnicalIndicatorsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $stockCode
    ) {
        $this->onQueue('indicators');
    }

    /**
     * Execute the job.
     */
    public function handle(TechnicalIndicatorService $indicatorService): void
    {
        try {
            Log::info('Updating technical indicators', [
                'code' => $this->stockCode,
            ]);

            $indicatorService->updateIndicatorsForStock($this->stockCode);

            Log::info('Technical indicators updated successfully', [
                'code' => $this->stockCode,
            ]);
        } catch (\Exception $e) {
            Log::error('UpdateTechnicalIndicatorsJob failed', [
                'code' => $this->stockCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('UpdateTechnicalIndicatorsJob permanently failed', [
            'code' => $this->stockCode,
            'error' => $exception->getMessage(),
        ]);
    }
}

