<?php

namespace App\Jobs;

use App\Models\Stock;
use App\Services\IdxApiService;
use App\Jobs\UpdateTechnicalIndicatorsJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Job to import historical data for a stock (10 years by default).
 * 
 * Queue: historical-data
 */
class ImportHistoricalDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 2;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $stockCode,
        private int $years = 10
    ) {
        $this->onQueue('historical-data');
    }

    /**
     * Execute the job.
     */
    public function handle(IdxApiService $idxApiService): void
    {
        try {
            $stock = Stock::where('code', $this->stockCode)->first();

            if (!$stock) {
                Log::warning('Stock not found for historical import', [
                    'code' => $this->stockCode,
                ]);
                return;
            }

            // Calculate date range
            $endDate = Carbon::today();
            $startDate = $endDate->copy()->subYears($this->years);

            // Adjust start date to stock listing date if available
            if ($stock->listing_date && $stock->listing_date->gt($startDate)) {
                $startDate = $stock->listing_date->copy();
            }

            Log::info('Starting historical data import', [
                'code' => $this->stockCode,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'years' => $this->years,
            ]);

            // Fetch historical prices
            $prices = $idxApiService->fetchHistoricalPrices($this->stockCode, $startDate, $endDate);

            if ($prices->isEmpty()) {
                Log::warning('No historical prices returned from API', [
                    'code' => $this->stockCode,
                ]);
                return;
            }

            $count = $prices->count();
            Log::info('Historical data import completed', [
                'code' => $this->stockCode,
                'prices_imported' => $count,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]);

            // Dispatch job to update technical indicators after import
            if ($count > 0) {
                UpdateTechnicalIndicatorsJob::dispatch($this->stockCode);
            }
        } catch (\Exception $e) {
            Log::error('ImportHistoricalDataJob failed', [
                'code' => $this->stockCode,
                'years' => $this->years,
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
        Log::error('ImportHistoricalDataJob permanently failed', [
            'code' => $this->stockCode,
            'years' => $this->years,
            'error' => $exception->getMessage(),
        ]);
    }
}

