<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScreenStocksRequest;
use App\Models\StockScreening;
use App\Services\StockScreeningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class StockScreeningController extends Controller
{
    public function __construct(
        private StockScreeningService $screeningService
    ) {}

    /**
     * Show stock screening page.
     *
     * @param Request $request
     * @return Response
     */
    public function show(Request $request): Response
    {
        $user = $request->user();
        
        // Get saved screenings for authenticated users
        $savedScreenings = collect([]);
        if ($user) {
            $savedScreenings = StockScreening::where('user_id', $user->id)
                ->where('is_saved', true)
                ->latest()
                ->get();
        }
        
        return Inertia::render('Stocks/StockScreening', [
            'savedScreenings' => $savedScreenings,
        ]);
    }

    /**
     * Screen stocks with filters.
     *
     * @param ScreenStocksRequest $request
     * @return JsonResponse
     */
    public function screen(ScreenStocksRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $user = $request->user();
            
            // Remove limit from filters (it's not a screening filter)
            $limit = $filters['limit'] ?? null;
            unset($filters['limit']);
            
            // Run screening
            $results = $this->screeningService->screen($filters, $user);
            
            // Apply limit if specified (in addition to tiered access limits)
            if ($limit !== null && $limit > 0) {
                $results = $results->take($limit);
            }
            
            // Prepare response data
            $responseData = $results->map(function ($stock) {
                $latestPrice = $stock->getLatestPrice();
                $latestIndicator = $stock->technicalIndicators()->latest('date')->first();
                $latestSignal = $stock->signals()->active()->latest('signal_date')->first();
                $latestPrediction = $stock->predictions()->latest('prediction_date')->first();
                
                return [
                    'id' => $stock->id,
                    'code' => $stock->code,
                    'name' => $stock->name,
                    'sector' => $stock->sector,
                    'sub_sector' => $stock->sub_sector,
                    'category' => $stock->category,
                    'market_cap' => $stock->market_cap,
                    'current_price' => $latestPrice ? (float) $latestPrice->close : null,
                    'price_change' => $latestPrice ? $latestPrice->getPriceChange() : null,
                    'price_change_percent' => $latestPrice ? $latestPrice->calculateReturns() : null,
                    'volume' => $latestPrice ? $latestPrice->volume : null,
                    'value' => $latestPrice ? (float) $latestPrice->value : null,
                    'rsi' => $latestIndicator ? $latestIndicator->rsi : null,
                    'macd' => $latestIndicator ? [
                        'macd' => $latestIndicator->macd,
                        'signal' => $latestIndicator->macd_signal,
                        'histogram' => $latestIndicator->macd_histogram,
                    ] : null,
                    'signal' => $latestSignal ? [
                        'type' => $latestSignal->signal_type,
                        'strength' => (float) $latestSignal->signal_strength,
                        'risk_level' => $latestSignal->risk_level,
                        'reason' => $latestSignal->reason,
                        'price_target' => $latestSignal->price_target,
                        'stop_loss' => $latestSignal->stop_loss,
                        'take_profit' => $latestSignal->take_profit,
                    ] : null,
                    'prediction' => $latestPrediction ? [
                        'predicted_price' => (float) $latestPrediction->predicted_price,
                        'confidence' => (float) $latestPrediction->confidence_score,
                        'horizon' => $latestPrediction->prediction_horizon,
                        'lower_bound' => $latestPrediction->lower_bound,
                        'upper_bound' => $latestPrediction->upper_bound,
                    ] : null,
                ];
            })->values();
            
            return response()->json([
                'success' => true,
                'data' => $responseData,
                'count' => $responseData->count(),
                'filters' => $filters,
            ]);
        } catch (\Exception $e) {
            Log::error('Stock screening failed', [
                'filters' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to screen stocks: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save screening criteria.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to save screenings.',
            ], 401);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'filters' => ['required', 'array'],
            'filters.sector' => ['nullable', 'array'],
            'filters.category' => ['nullable', 'array'],
            'filters.price_min' => ['nullable', 'numeric', 'min:0'],
            'filters.price_max' => ['nullable', 'numeric', 'min:0'],
            'filters.volume_min' => ['nullable', 'integer', 'min:0'],
            'filters.rsi_min' => ['nullable', 'numeric', 'between:0,100'],
            'filters.rsi_max' => ['nullable', 'numeric', 'between:0,100'],
            'filters.macd_bullish' => ['nullable', 'boolean'],
            'filters.signal_type' => ['nullable', 'in:buy,sell,hold'],
            'filters.signal_strength_min' => ['nullable', 'numeric', 'between:0,1'],
            'filters.risk_level' => ['nullable', 'array'],
            'filters.prediction_horizon' => ['nullable', 'in:1,7,30'],
            'filters.prediction_confidence_min' => ['nullable', 'numeric', 'between:0,1'],
        ]);

        try {
            $screening = $this->screeningService->saveScreening(
                $validated['filters'],
                $user,
                $validated['name']
            );
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $screening->id,
                    'name' => $screening->name,
                    'filters' => $screening->filters,
                    'created_at' => $screening->created_at,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to save screening', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save screening: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get saved screenings for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function saved(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to view saved screenings.',
            ], 401);
        }

        try {
            $screenings = $this->screeningService->getSavedScreenings($user);
            
            $responseData = $screenings->map(function ($screening) {
                return [
                    'id' => $screening->id,
                    'name' => $screening->name,
                    'filters' => $screening->filters,
                    'results_count' => $screening->results_count,
                    'last_run_at' => $screening->last_run_at,
                    'created_at' => $screening->created_at,
                    'updated_at' => $screening->updated_at,
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $responseData,
                'count' => $responseData->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get saved screenings', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get saved screenings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get screening results by ID.
     *
     * @param Request $request
     * @param StockScreening $screening
     * @return JsonResponse
     */
    public function results(Request $request, StockScreening $screening): JsonResponse
    {
        $user = $request->user();
        
        // Check if screening belongs to user or is public
        if ($screening->user_id && $screening->user_id !== $user?->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this screening.',
            ], 403);
        }

        try {
            // If results are expired or missing, rerun the screening
            if ($screening->isExpired() || empty($screening->results)) {
                $screening = $this->screeningService->runAndSaveResults($screening);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $screening->id,
                    'name' => $screening->name,
                    'filters' => $screening->filters,
                    'results' => $screening->results ?? [],
                    'results_count' => $screening->results_count,
                    'last_run_at' => $screening->last_run_at,
                    'created_at' => $screening->created_at,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get screening results', [
                'screening_id' => $screening->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get screening results: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a saved screening.
     *
     * @param Request $request
     * @param StockScreening $screening
     * @return JsonResponse
     */
    public function delete(Request $request, StockScreening $screening): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to delete screenings.',
            ], 401);
        }

        // Check if screening belongs to user
        if ($screening->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this screening.',
            ], 403);
        }

        try {
            $screening->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Screening deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete screening', [
                'screening_id' => $screening->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete screening: ' . $e->getMessage(),
            ], 500);
        }
    }
}

