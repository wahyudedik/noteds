<?php

namespace App\Jobs;

use App\Models\MlModel;
use App\Services\MLIntegrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TrainMLModelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $queue = 'ml-training';

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $stockCode,
        private string $modelType,
        private int $horizon = 1,
        private array $hyperparameters = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(MLIntegrationService $mlIntegrationService): void
    {
        try {
            Log::info('Starting model training job', [
                'stock_code' => $this->stockCode,
                'model_type' => $this->modelType,
                'horizon' => $this->horizon,
            ]);

            $model = $mlIntegrationService->trainModel(
                $this->stockCode,
                $this->modelType,
                $this->hyperparameters
            );

            Log::info('Model training job completed', [
                'model_id' => $model->id,
                'stock_code' => $this->stockCode,
                'model_type' => $this->modelType,
            ]);
        } catch (\Exception $e) {
            Log::error('Model training job failed', [
                'stock_code' => $this->stockCode,
                'model_type' => $this->modelType,
                'horizon' => $this->horizon,
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
        Log::error('Model training job failed permanently', [
            'stock_code' => $this->stockCode,
            'model_type' => $this->modelType,
            'horizon' => $this->horizon,
            'error' => $exception->getMessage(),
        ]);

        // Optionally, mark any related models as failed
        // This would require finding the model by stock_code and model_type
    }
}

