<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\MlModel;
use App\Models\StockPrediction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MLDashboardController extends Controller
{
    /**
     * Get model performance metrics dashboard data
     */
    public function metrics(Request $request): JsonResponse
    {
        $stockCode = $request->get('stock_code');
        $horizon = $request->get('prediction_horizon', 1);
        $days = $request->get('days', 30);

        $query = MlModel::with('stock');

        if ($stockCode) {
            $stock = Stock::where('code', $stockCode)->first();
            if ($stock) {
                $query->where('stock_id', $stock->id);
            }
        }

        if ($horizon) {
            $query->where('prediction_horizon', $horizon);
        }

        $models = $query->where('status', 'active')
            ->whereNotNull('metrics')
            ->latest('training_completed_at')
            ->get();

        // Calculate aggregate metrics
        $metrics = [
            'total_models' => $models->count(),
            'models_by_type' => $models->groupBy('model_type')->map->count(),
            'average_accuracy' => 0,
            'average_mae' => 0,
            'average_rmse' => 0,
            'best_model' => null,
        ];

        if ($models->isNotEmpty()) {
            $accuracies = [];
            $maes = [];
            $rmses = [];

            foreach ($models as $model) {
                $modelMetrics = $model->metrics ?? [];
                
                if (isset($modelMetrics['accuracy'])) {
                    $accuracies[] = $modelMetrics['accuracy'];
                }
                if (isset($modelMetrics['mae'])) {
                    $maes[] = $modelMetrics['mae'];
                }
                if (isset($modelMetrics['rmse'])) {
                    $rmses[] = $modelMetrics['rmse'];
                }
            }

            $metrics['average_accuracy'] = !empty($accuracies) ? array_sum($accuracies) / count($accuracies) : 0;
            $metrics['average_mae'] = !empty($maes) ? array_sum($maes) / count($maes) : 0;
            $metrics['average_rmse'] = !empty($rmses) ? array_sum($rmses) / count($rmses) : 0;

            // Find best model
            $bestModel = $models->sortByDesc(function ($model) {
                $m = $model->metrics ?? [];
                return $m['accuracy'] ?? $m['r2'] ?? 0;
            })->first();

            $metrics['best_model'] = [
                'id' => $bestModel->id,
                'model_type' => $bestModel->model_type,
                'accuracy' => $bestModel->metrics['accuracy'] ?? null,
            ];
        }

        return response()->json($metrics);
    }

    /**
     * Get prediction accuracy over time
     */
    public function accuracyOverTime(Request $request): JsonResponse
    {
        $stockCode = $request->get('stock_code');
        $horizon = $request->get('prediction_horizon', 1);
        $days = $request->get('days', 30);

        $query = StockPrediction::with('stock', 'mlModel')
            ->whereNotNull('actual_price')
            ->where('target_date', '>=', Carbon::now()->subDays($days));

        if ($stockCode) {
            $stock = Stock::where('code', $stockCode)->first();
            if ($stock) {
                $query->where('stock_id', $stock->id);
            }
        }

        if ($horizon) {
            $query->where('prediction_horizon', $horizon);
        }

        $predictions = $query->orderBy('target_date')
            ->get()
            ->groupBy(function ($prediction) {
                return Carbon::parse($prediction->target_date)->format('Y-m-d');
            })
            ->map(function ($group) {
                $accuracies = $group->map(function ($pred) {
                    return $pred->getPredictionAccuracy();
                })->filter();

                return [
                    'date' => $group->first()->target_date,
                    'count' => $group->count(),
                    'average_accuracy' => $accuracies->isNotEmpty() ? $accuracies->avg() : null,
                    'min_accuracy' => $accuracies->isNotEmpty() ? $accuracies->min() : null,
                    'max_accuracy' => $accuracies->isNotEmpty() ? $accuracies->max() : null,
                ];
            })
            ->values();

        return response()->json([
            'data' => $predictions,
            'period' => $days,
            'stock_code' => $stockCode,
        ]);
    }

    /**
     * Get model comparison data
     */
    public function modelComparison(Request $request): JsonResponse
    {
        $stockCode = $request->get('stock_code');
        $horizon = $request->get('prediction_horizon', 1);

        if (!$stockCode) {
            return response()->json(['error' => 'stock_code is required'], 400);
        }

        $stock = Stock::where('code', $stockCode)->first();
        if (!$stock) {
            return response()->json(['error' => 'Stock not found'], 404);
        }

        $models = MlModel::where('stock_id', $stock->id)
            ->where('prediction_horizon', $horizon)
            ->where('status', 'active')
            ->whereNotNull('metrics')
            ->get();

        $comparison = $models->map(function ($model) {
            $metrics = $model->metrics ?? [];
            return [
                'model_id' => $model->id,
                'model_type' => $model->model_type,
                'version' => $model->model_version,
                'is_best' => $model->is_best_model,
                'metrics' => [
                    'accuracy' => $metrics['accuracy'] ?? null,
                    'mae' => $metrics['mae'] ?? null,
                    'rmse' => $metrics['rmse'] ?? null,
                    'mape' => $metrics['mape'] ?? null,
                    'r2' => $metrics['r2'] ?? null,
                ],
                'training_date' => $model->training_completed_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'stock_code' => $stockCode,
            'prediction_horizon' => $horizon,
            'models' => $comparison,
        ]);
    }
}

