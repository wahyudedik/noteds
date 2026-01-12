<?php

namespace App\Jobs;

use App\Models\Stock;
use App\Services\MLIntegrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GeneratePredictionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $queue = 'ml-inference';

    /**
     * Create a new job instance.
     */
    public function __construct(
        private ?string $stockCode = null,
        private int $horizon = 1,
        private ?string $modelType = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(MLIntegrationService $mlIntegrationService): void
    {
        try {
            if ($this->stockCode) {
                // Generate prediction for specific stock
                Log::info('Generating prediction for stock', [
                    'stock_code' => $this->stockCode,
                    'horizon' => $this->horizon,
                    'model_type' => $this->modelType,
                ]);

                $prediction = $mlIntegrationService->requestPrediction(
                    $this->stockCode,
                    $this->horizon,
                    $this->modelType
                );

                Log::info('Prediction generated successfully', [
                    'prediction_id' => $prediction->id,
                    'stock_code' => $this->stockCode,
                    'predicted_price' => $prediction->predicted_price,
                ]);
            } else {
                // Generate predictions for all active stocks
                Log::info('Generating predictions for all active stocks', [
                    'horizon' => $this->horizon,
                ]);

                $stocks = Stock::active()->get();
                $successCount = 0;
                $failureCount = 0;

                foreach ($stocks as $stock) {
                    try {
                        $prediction = $mlIntegrationService->requestPrediction(
                            $stock->code,
                            $this->horizon,
                            $this->modelType
                        );
                        $successCount++;
                    } catch (\Exception $e) {
                        $failureCount++;
                        Log::warning('Failed to generate prediction for stock', [
                            'stock_code' => $stock->code,
                            'error' => $e->getMessage(),
                        ]);
                        // Continue with other stocks
                    }
                }

                Log::info('Bulk prediction generation completed', [
                    'total_stocks' => $stocks->count(),
                    'success_count' => $successCount,
                    'failure_count' => $failureCount,
                    'horizon' => $this->horizon,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Prediction generation job failed', [
                'stock_code' => $this->stockCode,
                'horizon' => $this->horizon,
                'model_type' => $this->modelType,
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
        Log::error('Prediction generation job failed permanently', [
            'stock_code' => $this->stockCode,
            'horizon' => $this->horizon,
            'model_type' => $this->modelType,
            'error' => $exception->getMessage(),
        ]);
    }
}

