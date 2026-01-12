<?php

namespace App\Jobs;

use App\Models\Stock;
use App\Models\StockPrediction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckPredictionAccuracyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ?string $stockCode = null
    ) {
        $this->onQueue('ml-inference');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if ($this->stockCode) {
                $stock = Stock::where('code', $this->stockCode)->first();
                if ($stock) {
                    $this->checkAccuracyForStock($stock);
                } else {
                    Log::warning('Stock not found for accuracy check', ['code' => $this->stockCode]);
                }
            } else {
                // Check accuracy for all active stocks
                $stocks = Stock::active()->get();
                
                foreach ($stocks as $stock) {
                    $this->checkAccuracyForStock($stock);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error checking prediction accuracy', [
                'stock_code' => $this->stockCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Check accuracy for a single stock.
     */
    protected function checkAccuracyForStock(Stock $stock): void
    {
        try {
            // Get predictions where target_date has passed and we have actual prices
            $predictions = StockPrediction::where('stock_id', $stock->id)
                ->where('target_date', '<=', Carbon::today())
                ->whereNull('prediction_error')
                ->whereNotNull('actual_price')
                ->get();

            foreach ($predictions as $prediction) {
                $error = $prediction->calculateError();
                if ($error !== null) {
                    Log::info('Prediction accuracy calculated', [
                        'stock_code' => $stock->code,
                        'prediction_id' => $prediction->id,
                        'error' => $error,
                        'accuracy' => $prediction->getPredictionAccuracy(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error checking accuracy for stock', [
                'stock_code' => $stock->code,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

