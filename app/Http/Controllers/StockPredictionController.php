<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\StockPrediction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class StockPredictionController extends Controller
{
    /**
     * Get predictions for stock(s) (tiered access).
     */
    public function predict(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'stock_codes' => ['required', 'array', 'min:1', 'max:10'],
            'stock_codes.*' => ['required', 'string', 'exists:stocks,code'],
            'horizon' => ['nullable', 'integer', 'in:1,7,30'],
        ]);

        // Check daily prediction limit
        $limit = $this->getPredictionLimit($user);
        if ($limit > 0) {
            $cacheKey = "user_predictions_{$user->id}_" . Carbon::today()->toDateString();
            $used = Cache::get($cacheKey, 0);

            if ($used >= $limit) {
                return response()->json([
                    'error' => "Daily prediction limit reached ({$limit}). Upgrade to premium for unlimited predictions.",
                ], 429);
            }

            Cache::put($cacheKey, $used + count($request->stock_codes), Carbon::tomorrow());
        }

        $stocks = Stock::whereIn('code', $request->stock_codes)->get();
        $predictions = collect();

        foreach ($stocks as $stock) {
            $query = $stock->predictions()
                ->with('mlModel')
                ->where('prediction_date', '>=', Carbon::today()->subDays(7));

            if ($request->has('horizon')) {
                $query->where('prediction_horizon', $request->horizon);
            }

            $stockPredictions = $query->latest('prediction_date')->get();
            $predictions = $predictions->merge($stockPredictions);
        }

        return response()->json([
            'predictions' => $predictions,
            'stocks' => $stocks,
        ]);
    }

    /**
     * Get prediction accuracy metrics.
     */
    public function accuracy(Stock $stock, Request $request)
    {
        $user = $request->user();

        $predictions = $stock->predictions()
            ->whereNotNull('actual_price')
            ->where('target_date', '<=', Carbon::today())
            ->latest('target_date')
            ->limit(100)
            ->get();

        $metrics = [
            'total_predictions' => $predictions->count(),
            'accurate_predictions' => 0,
            'average_error' => 0.0,
            'average_accuracy' => 0.0,
            'mae' => 0.0, // Mean Absolute Error
            'rmse' => 0.0, // Root Mean Squared Error
        ];

        if ($predictions->isEmpty()) {
            return response()->json($metrics);
        }

        $errors = [];
        $accuracies = [];

        foreach ($predictions as $prediction) {
            $error = abs($prediction->actual_price - $prediction->predicted_price);
            $errors[] = $error;
            
            $accuracy = $prediction->getPredictionAccuracy();
            if ($accuracy !== null) {
                $accuracies[] = $accuracy;
            }

            if ($prediction->isAccurate(0.05)) { // 5% threshold
                $metrics['accurate_predictions']++;
            }
        }

        if (!empty($errors)) {
            $metrics['average_error'] = array_sum($errors) / count($errors);
            $metrics['mae'] = $metrics['average_error'];
            $metrics['rmse'] = sqrt(array_sum(array_map(fn($e) => $e * $e, $errors)) / count($errors));
        }

        if (!empty($accuracies)) {
            $metrics['average_accuracy'] = array_sum($accuracies) / count($accuracies);
        }

        $metrics['accuracy_rate'] = $metrics['total_predictions'] > 0
            ? ($metrics['accurate_predictions'] / $metrics['total_predictions']) * 100
            : 0;

        return response()->json($metrics);
    }

    /**
     * Get prediction limit for user.
     */
    protected function getPredictionLimit($user): int
    {
        // Check if user is premium
        $isPremium = $this->isPremiumUser($user);

        if ($isPremium) {
            return -1; // Unlimited
        }

        return config('stocks.free_tier_limits.predictions_per_day', 10);
    }

    /**
     * Check if user is premium.
     */
    protected function isPremiumUser($user): bool
    {
        // Implement your premium user check logic
        return false; // TODO: Implement actual premium check
    }
}

