<?php

namespace App\Jobs;

use App\Models\Stock;
use App\Services\IdxApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Job to collect stock price data for a specific stock and date.
 * 
 * Queue: stock-data
 */
class CollectStockDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $stockCode,
        private ?Carbon $date = null
    ) {
        $this->onQueue('stock-data');
    }

    /**
     * Execute the job.
     */
    public function handle(IdxApiService $idxApiService): void
    {
        try {
            $stock = Stock::where('code', $this->stockCode)->first();

            if (!$stock) {
                Log::warning('Stock not found for data collection', [
                    'code' => $this->stockCode,
                ]);
                return;
            }

            $date = $this->date ?? Carbon::today();

            // Skip if it's a weekend (market is closed)
            if ($date->isWeekend()) {
                Log::info('Skipping stock data collection for weekend', [
                    'code' => $this->stockCode,
                    'date' => $date->format('Y-m-d'),
                ]);
                return;
            }

            $price = $idxApiService->fetchStockPrice($this->stockCode, $date);

            if ($price) {
                Log::info('Stock price collected successfully', [
                    'code' => $this->stockCode,
                    'date' => $date->format('Y-m-d'),
                    'close' => $price->close,
                ]);
            } else {
                Log::warning('Failed to collect stock price', [
                    'code' => $this->stockCode,
                    'date' => $date->format('Y-m-d'),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('CollectStockDataJob failed', [
                'code' => $this->stockCode,
                'date' => $this->date?->format('Y-m-d'),
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
        Log::error('CollectStockDataJob permanently failed', [
            'code' => $this->stockCode,
            'date' => $this->date?->format('Y-m-d'),
            'error' => $exception->getMessage(),
        ]);
    }
}

