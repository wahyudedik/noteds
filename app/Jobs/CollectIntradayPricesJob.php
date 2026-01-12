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
 * Job to collect real-time intraday prices for all active stocks.
 * 
 * Queue: stock-data
 * Runs: Every minute during market hours (9:00-16:00 WIB, weekdays only)
 */
class CollectIntradayPricesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->onQueue('stock-data');
    }

    /**
     * Execute the job.
     */
    public function handle(IdxApiService $idxApiService): void
    {
        try {
            $now = Carbon::now('Asia/Jakarta');

            // Check if market is open (weekdays, 9:00-16:00 WIB)
            if ($now->isWeekend()) {
                Log::info('Market is closed (weekend), skipping intraday collection');
                return;
            }

            $marketOpen = Carbon::createFromTime(9, 0, 0, 'Asia/Jakarta');
            $marketClose = Carbon::createFromTime(16, 0, 0, 'Asia/Jakarta');

            if ($now->lt($marketOpen) || $now->gt($marketClose)) {
                Log::info('Market is closed, skipping intraday collection', [
                    'current_time' => $now->format('H:i:s'),
                ]);
                return;
            }

            // Get all active stocks
            $stocks = Stock::active()->get();

            if ($stocks->isEmpty()) {
                Log::warning('No active stocks found for intraday collection');
                return;
            }

            $collected = 0;
            $failed = 0;

            foreach ($stocks as $stock) {
                try {
                    $price = $idxApiService->fetchIntradayPrice($stock->code);

                    if ($price) {
                        $collected++;
                        Log::debug('Intraday price collected', [
                            'code' => $stock->code,
                            'price' => $price->close,
                            'timestamp' => $price->timestamp?->format('Y-m-d H:i:s'),
                        ]);
                    } else {
                        $failed++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    Log::error('Failed to collect intraday price for stock', [
                        'code' => $stock->code,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Intraday price collection completed', [
                'total_stocks' => $stocks->count(),
                'collected' => $collected,
                'failed' => $failed,
            ]);
        } catch (\Exception $e) {
            Log::error('CollectIntradayPricesJob failed', [
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
        Log::error('CollectIntradayPricesJob permanently failed', [
            'error' => $exception->getMessage(),
        ]);
    }
}

