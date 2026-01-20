<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\MlModel;
use App\Models\StockPrediction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MLIntegrationService
{
    /**
     * Request prediction for a stock.
     *
     * @param string $stockCode
     * @param int $horizon Days ahead for prediction (1, 7, 30)
     * @param string|null $modelType Specific model type to use (optional)
     * @return StockPrediction
     * @throws \Exception
     */
    public function requestPrediction(string $stockCode, int $horizon = 1, ?string $modelType = null): StockPrediction
    {
        $stock = Stock::where('code', $stockCode)->first();
        
        if (!$stock) {
            throw new \Exception("Stock not found: {$stockCode}");
        }

        // Validate horizon
        if (!in_array($horizon, config('stocks.prediction_horizons', [1, 7, 30]))) {
            throw new \Exception("Invalid prediction horizon: {$horizon}");
        }

        // Select best model for this stock and horizon
        $model = $this->selectBestModel($stock->id, $horizon);
        
        if (!$model) {
            throw new \Exception("No active model found for stock {$stockCode} with horizon {$horizon}");
        }

        // Override model type if specified
        if ($modelType && $model->model_type !== $modelType) {
            $model = MlModel::where('stock_id', $stock->id)
                ->where('prediction_horizon', $horizon)
                ->where('model_type', $modelType)
                ->where('status', 'active')
                ->first();
            
            if (!$model) {
                throw new \Exception("Model type {$modelType} not found for stock {$stockCode} with horizon {$horizon}");
            }
        }

        try {
            // Get latest price data for the model
            $latestPrice = $stock->getLatestPrice();
            
            if (!$latestPrice) {
                throw new \Exception("No price data available for stock {$stockCode}");
            }

            // Prepare data for ML service
            $data = [
                'stock_code' => $stockCode,
                'stock_id' => $stock->id,
                'model_id' => $model->id,
                'model_type' => $model->model_type,
                'horizon' => $horizon,
                'current_price' => (float) $latestPrice->close,
                'current_date' => Carbon::now()->format('Y-m-d'),
            ];

            // Call ML service API
            $response = Http::timeout(config('services.ml_service.timeout', 300))
                ->withHeaders([
                    'Authorization' => 'Bearer ' . config('services.ml_service.api_key'),
                    'Content-Type' => 'application/json',
                ])
                ->post(config('services.ml_service.base_url') . '/api/ml/predict', $data);

            if (!$response->successful()) {
                throw new \Exception("ML service error: " . $response->body());
            }

            $predictionData = $response->json();

            // Calculate target date
            $targetDate = Carbon::now()->addDays($horizon)->format('Y-m-d');

            // Create prediction record
            $prediction = StockPrediction::updateOrCreate(
                [
                    'stock_id' => $stock->id,
                    'ml_model_id' => $model->id,
                    'prediction_date' => Carbon::now()->format('Y-m-d'),
                    'target_date' => $targetDate,
                ],
                [
                    'predicted_price' => $predictionData['predicted_price'] ?? null,
                    'confidence_score' => $predictionData['confidence_score'] ?? 0.5,
                    'lower_bound' => $predictionData['lower_bound'] ?? null,
                    'upper_bound' => $predictionData['upper_bound'] ?? null,
                    'prediction_horizon' => $horizon,
                    'metadata' => $predictionData['metadata'] ?? [],
                ]
            );

            Log::info('Prediction generated successfully', [
                'stock_code' => $stockCode,
                'model_id' => $model->id,
                'horizon' => $horizon,
                'predicted_price' => $prediction->predicted_price,
            ]);

            return $prediction;
        } catch (\Exception $e) {
            Log::error('Failed to generate prediction', [
                'stock_code' => $stockCode,
                'horizon' => $horizon,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Request bulk predictions for multiple stocks.
     *
     * @param array $stockCodes
     * @param int $horizon
     * @return Collection
     */
    public function requestBulkPrediction(array $stockCodes, int $horizon = 1): Collection
    {
        $predictions = collect();

        foreach ($stockCodes as $stockCode) {
            try {
                $prediction = $this->requestPrediction($stockCode, $horizon);
                $predictions->push($prediction);
            } catch (\Exception $e) {
                Log::warning('Failed to generate prediction for stock', [
                    'stock_code' => $stockCode,
                    'error' => $e->getMessage(),
                ]);
                // Continue with other stocks
            }
        }

        return $predictions;
    }

    /**
     * Train a model for a stock.
     *
     * @param string $stockCode
     * @param string $modelType lstm, transformer, cnn_lstm
     * @param array $hyperparameters
     * @return MlModel
     * @throws \Exception
     */
    public function trainModel(string $stockCode, string $modelType, array $hyperparameters = []): MlModel
    {
        $stock = Stock::where('code', $stockCode)->first();
        
        if (!$stock) {
            throw new \Exception("Stock not found: {$stockCode}");
        }

        // Validate model type
        $validTypes = ['lstm', 'transformer', 'cnn_lstm'];
        if (!in_array($modelType, $validTypes)) {
            throw new \Exception("Invalid model type: {$modelType}");
        }

        try {
            // Create model record with training status
            $model = MlModel::create([
                'model_type' => $modelType,
                'stock_id' => $stock->id,
                'model_version' => '1.0.0', // Can be improved with versioning logic
                'status' => 'training',
                'training_started_at' => Carbon::now(),
                'hyperparameters' => $hyperparameters,
                'prediction_horizon' => 1, // Default, can be parameterized
                'is_best_model' => false,
            ]);

            // Prepare data for ML service
            $data = [
                'model_id' => $model->id,
                'stock_code' => $stockCode,
                'stock_id' => $stock->id,
                'model_type' => $modelType,
                'hyperparameters' => $hyperparameters,
            ];

            // Call ML service API to start training (async)
            $response = Http::timeout(config('services.ml_service.timeout', 300))
                ->withHeaders([
                    'Authorization' => 'Bearer ' . config('services.ml_service.api_key'),
                    'Content-Type' => 'application/json',
                ])
                ->post(config('services.ml_service.base_url') . '/api/ml/train', $data);

            if (!$response->successful()) {
                $model->update(['status' => 'failed']);
                throw new \Exception("ML service error: " . $response->body());
            }

            $trainingData = $response->json();

            // Update model with training information
            $model->update([
                'file_path' => $trainingData['file_path'] ?? null,
            ]);

            Log::info('Model training started', [
                'model_id' => $model->id,
                'stock_code' => $stockCode,
                'model_type' => $modelType,
            ]);

            return $model;
        } catch (\Exception $e) {
            Log::error('Failed to train model', [
                'stock_code' => $stockCode,
                'model_type' => $modelType,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Select best model for a stock and prediction horizon.
     *
     * @param string $stockId
     * @param int $horizon
     * @return MlModel|null
     */
    public function selectBestModel(string $stockId, int $horizon): ?MlModel
    {
        // First, try to get the best model marked as best
        $bestModel = MlModel::getBestModelForStock($stockId, $horizon);
        
        if ($bestModel) {
            return $bestModel;
        }

        // If no best model, get any active model for this stock/horizon
        $model = MlModel::where('stock_id', $stockId)
            ->where('prediction_horizon', $horizon)
            ->where('status', 'active')
            ->orderBy('training_completed_at', 'desc')
            ->first();

        return $model;
    }

    /**
     * Get model metrics.
     *
     * @param string $modelId
     * @return array
     * @throws \Exception
     */
    public function getModelMetrics(string $modelId): array
    {
        $model = MlModel::find($modelId);
        
        if (!$model) {
            throw new \Exception("Model not found: {$modelId}");
        }

        // Try to get metrics from database first
        if ($model->metrics && !empty($model->metrics)) {
            return $model->metrics;
        }

        // Fetch from ML service if not in database
        try {
            $response = Http::timeout(config('services.ml_service.timeout', 300))
                ->withHeaders([
                    'Authorization' => 'Bearer ' . config('services.ml_service.api_key'),
                ])
                ->get(config('services.ml_service.base_url') . '/api/ml/metrics/' . $modelId);

            if ($response->successful()) {
                $metrics = $response->json();
                
                // Update model with metrics
                $model->update(['metrics' => $metrics]);
                
                return $metrics;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch model metrics from ML service', [
                'model_id' => $modelId,
                'error' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * Check training status.
     *
     * @param string $modelId
     * @return string training, active, archived, failed
     * @throws \Exception
     */
    public function checkTrainingStatus(string $modelId): string
    {
        $model = MlModel::find($modelId);
        
        if (!$model) {
            throw new \Exception("Model not found: {$modelId}");
        }

        // Check status in database first
        if ($model->status !== 'training') {
            return $model->status;
        }

        // Fetch status from ML service
        try {
            $response = Http::timeout(config('services.ml_service.timeout', 300))
                ->withHeaders([
                    'Authorization' => 'Bearer ' . config('services.ml_service.api_key'),
                ])
                ->get(config('services.ml_service.base_url') . '/api/ml/status/' . $modelId);

            if ($response->successful()) {
                $statusData = $response->json();
                $status = $statusData['status'] ?? $model->status;
                
                // Update model status if changed
                if ($status !== $model->status) {
                    $updateData = ['status' => $status];
                    
                    if ($status === 'active' && !$model->training_completed_at) {
                        $updateData['training_completed_at'] = Carbon::now();
                    }
                    
                    if (isset($statusData['metrics'])) {
                        $updateData['metrics'] = $statusData['metrics'];
                    }
                    
                    if (isset($statusData['file_path'])) {
                        $updateData['file_path'] = $statusData['file_path'];
                    }
                    
                    $model->update($updateData);
                    
                    // Auto-select best model if training just completed
                    if ($status === 'active' && $model->status === 'training') {
                        try {
                            $this->autoSelectBestModel($model->stock->code ?? '', $model->prediction_horizon ?? 1);
                        } catch (\Exception $e) {
                            Log::warning('Failed to auto-select best model after training', [
                                'model_id' => $model->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }
                
                return $status;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to check training status from ML service', [
                'model_id' => $modelId,
                'error' => $e->getMessage(),
            ]);
        }

        return $model->status;
    }

    /**
     * Auto-select best model after training completion.
     *
     * @param string $stockCode
     * @param int $horizon
     * @return MlModel|null
     */
    public function autoSelectBestModel(string $stockCode, int $horizon = 1): ?MlModel
    {
        $stock = Stock::where('code', $stockCode)->first();
        
        if (!$stock) {
            Log::warning('Stock not found for auto-selection', ['stock_code' => $stockCode]);
            return null;
        }

        try {
            // Get all active models for this stock and horizon
            $models = MlModel::where('stock_id', $stock->id)
                ->where('prediction_horizon', $horizon)
                ->where('status', 'active')
                ->whereNotNull('metrics')
                ->get();

            if ($models->isEmpty()) {
                Log::info('No active models found for auto-selection', [
                    'stock_code' => $stockCode,
                    'horizon' => $horizon
                ]);
                return null;
            }

            // Prepare model results for comparison
            $modelResults = $models->map(function ($model) {
                return [
                    'model_id' => $model->id,
                    'model_type' => $model->model_type,
                    'version' => $model->model_version,
                    'metrics' => $model->metrics ?? [],
                    'created_at' => $model->training_completed_at?->toIso8601String()
                ];
            })->toArray();

            // Call ML service to compare and select best
            $response = Http::timeout(30)
                ->withHeaders([
                    'X-API-Key' => config('services.ml_service.api_key'),
                ])
                ->post(config('services.ml_service.base_url') . '/api/ml/select-best', [
                    'stock_code' => $stockCode,
                    'prediction_horizon' => $horizon,
                    'model_results' => $modelResults
                ]);

            if ($response->successful()) {
                $comparison = $response->json();
                $bestModelIndex = $comparison['best_model_index'] ?? null;

                if ($bestModelIndex !== null && isset($models[$bestModelIndex])) {
                    $bestModel = $models[$bestModelIndex];

                    // Deactivate all other models
                    MlModel::where('stock_id', $stock->id)
                        ->where('prediction_horizon', $horizon)
                        ->where('id', '!=', $bestModel->id)
                        ->update(['is_best_model' => false]);

                    // Mark best model
                    $bestModel->update(['is_best_model' => true]);

                    Log::info('Best model auto-selected', [
                        'stock_code' => $stockCode,
                        'horizon' => $horizon,
                        'best_model_id' => $bestModel->id,
                        'model_type' => $bestModel->model_type
                    ]);

                    return $bestModel;
                }
            } else {
                Log::warning('Failed to get best model from ML service', [
                    'stock_code' => $stockCode,
                    'response' => $response->body()
                ]);
            }

            // Fallback: select model with best accuracy
            $bestModel = $models->sortByDesc(function ($model) {
                $metrics = $model->metrics ?? [];
                return $metrics['accuracy'] ?? $metrics['r2'] ?? $metrics['mae'] ?? 0;
            })->first();

            if ($bestModel) {
                MlModel::where('stock_id', $stock->id)
                    ->where('prediction_horizon', $horizon)
                    ->where('id', '!=', $bestModel->id)
                    ->update(['is_best_model' => false]);

                $bestModel->update(['is_best_model' => true]);

                Log::info('Best model selected (fallback)', [
                    'stock_code' => $stockCode,
                    'best_model_id' => $bestModel->id
                ]);
            }

            return $bestModel;
        } catch (\Exception $e) {
            Log::error('Error in auto-selection', [
                'stock_code' => $stockCode,
                'horizon' => $horizon,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}

