<?php

namespace App\Http\Controllers;

use App\Services\MLIntegrationService;
use App\Models\MlModel;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MLModelController extends Controller
{
    public function __construct(
        private MLIntegrationService $mlService
    ) {}

    /**
     * List all models with optional filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = MlModel::with('stock');

        // Filters
        if ($request->has('stock_code')) {
            $stock = Stock::where('code', $request->stock_code)->first();
            if ($stock) {
                $query->where('stock_id', $stock->id);
            }
        }

        if ($request->has('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('prediction_horizon')) {
            $query->where('prediction_horizon', $request->prediction_horizon);
        }

        $models = $query->latest('training_completed_at')->paginate(20);

        return response()->json($models);
    }

    /**
     * Get model details
     */
    public function show(MlModel $model): JsonResponse
    {
        $model->load('stock', 'predictions');

        return response()->json($model);
    }

    /**
     * Activate a model
     */
    public function activate(MlModel $model): JsonResponse
    {
        $model->update(['status' => 'active', 'is_best_model' => false]);

        Log::info('Model activated', ['model_id' => $model->id]);

        return response()->json([
            'message' => 'Model activated successfully',
            'model' => $model
        ]);
    }

    /**
     * Deactivate a model
     */
    public function deactivate(MlModel $model): JsonResponse
    {
        $model->update(['status' => 'inactive']);

        Log::info('Model deactivated', ['model_id' => $model->id]);

        return response()->json([
            'message' => 'Model deactivated successfully',
            'model' => $model
        ]);
    }

    /**
     * Delete/archive a model
     */
    public function destroy(MlModel $model): JsonResponse
    {
        $model->update(['status' => 'archived']);

        Log::info('Model archived', ['model_id' => $model->id]);

        return response()->json([
            'message' => 'Model archived successfully'
        ]);
    }

    /**
     * Get models for a specific stock
     */
    public function getStockModels(Stock $stock, Request $request): JsonResponse
    {
        $query = $stock->mlModels()->with('predictions');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $models = $query->latest('training_completed_at')->get();

        return response()->json([
            'stock_code' => $stock->code,
            'count' => $models->count(),
            'models' => $models
        ]);
    }

    /**
     * Compare models for a stock
     */
    public function compare(Stock $stock, Request $request): JsonResponse
    {
        $horizon = $request->get('prediction_horizon', 1);

        $models = $stock->mlModels()
            ->where('prediction_horizon', $horizon)
            ->where('status', 'active')
            ->whereNotNull('metrics')
            ->get();

        if ($models->isEmpty()) {
            return response()->json([
                'stock_code' => $stock->code,
                'message' => 'No models available for comparison'
            ]);
        }

        // Prepare model results for comparison
        $modelResults = $models->map(function ($model) {
            return [
                'model_id' => $model->id,
                'model_type' => $model->model_type,
                'version' => $model->model_version,
                'metrics' => $model->metrics,
                'created_at' => $model->training_completed_at
            ];
        })->toArray();

        // Call ML service for comparison
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->withHeaders([
                    'X-API-Key' => config('services.ml_service.api_key'),
                ])
                ->post(config('services.ml_service.base_url') . '/api/ml/select-best', [
                    'stock_code' => $stock->code,
                    'prediction_horizon' => $horizon,
                    'model_results' => $modelResults
                ]);

            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            Log::warning('Failed to compare models via ML service', ['error' => $e->getMessage()]);
        }

        // Fallback: simple comparison
        $bestModel = $models->sortByDesc(function ($model) {
            $metrics = $model->metrics ?? [];
            return $metrics['accuracy'] ?? $metrics['r2'] ?? 0;
        })->first();

        return response()->json([
            'stock_code' => $stock->code,
            'best_model_id' => $bestModel->id,
            'models' => $models,
            'comparison_method' => 'fallback'
        ]);
    }
}

