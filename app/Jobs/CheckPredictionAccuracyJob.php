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
            // Get predictions where target_date has passed
            $predictions = StockPrediction::where('stock_id', $stock->id)
                ->where('target_date', '<=', Carbon::today())
                ->whereNull('actual_price') // Only process predictions without actual price yet
                ->get();

            $updatedCount = 0;
            
            foreach ($predictions as $prediction) {
                // Get actual price for target_date
                $targetDate = Carbon::parse($prediction->target_date);
                $actualPrice = $stock->getPriceAt($targetDate);
                
                if ($actualPrice) {
                    // Update prediction with actual price
                    $prediction->update([
                        'actual_price' => $actualPrice->close,
                    ]);
                    
                    // Calculate error and accuracy
                    $error = $prediction->calculateError();
                    $accuracy = $prediction->getPredictionAccuracy();
                    
                    $updatedCount++;
                    
                    Log::info('Prediction accuracy updated', [
                        'stock_code' => $stock->code,
                        'prediction_id' => $prediction->id,
                        'target_date' => $targetDate->format('Y-m-d'),
                        'predicted_price' => $prediction->predicted_price,
                        'actual_price' => $actualPrice->close,
                        'error' => $error,
                        'accuracy' => $accuracy,
                    ]);
                } else {
                    Log::warning('Actual price not found for prediction', [
                        'stock_code' => $stock->code,
                        'prediction_id' => $prediction->id,
                        'target_date' => $targetDate->format('Y-m-d'),
                    ]);
                }
            }
            
            // Also recalculate accuracy for predictions that already have actual_price
            $existingPredictions = StockPrediction::where('stock_id', $stock->id)
                ->where('target_date', '<=', Carbon::today())
                ->whereNotNull('actual_price')
                ->whereNull('prediction_error')
                ->get();
            
            foreach ($existingPredictions as $prediction) {
                $prediction->calculateError();
            }
            
            if ($updatedCount > 0) {
                Log::info('Prediction accuracy check completed', [
                    'stock_code' => $stock->code,
                    'updated_count' => $updatedCount,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error checking accuracy for stock', [
                'stock_code' => $stock->code,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

