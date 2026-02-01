<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScreenStocksRequest;
use App\Models\PortfolioRecommendation;
use App\Services\StockScreeningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GatewayController extends Controller
{
    public function __construct(
        private StockScreeningService $screeningService
    ) {}

    public function stocksDashboard(Request $request)
    {
        try {
            $user = $request->user();
            $perPage = (int) $request->input('per_page', 10);
            $page = (int) $request->input('page', 1);

            $watchlist = collect([]);
            if ($user) {
                $watchlist = $user->stockWatchlists()
                    ->with(['stock.prices' => function ($q) {
                        $q->latest('date')->limit(1);
                    }])
                    ->latest()
                    ->paginate($perPage, ['*'], 'page', $page);
            }

            $recentPredictions = Cache::remember('stocks:dashboard:recent_predictions', now()->addSeconds(60), function () {
                return \App\Models\StockPrediction::query()
                    ->with(['stock', 'mlModel'])
                    ->latest('prediction_date')
                    ->limit(5)
                    ->get();
            });
            $activeSignals = Cache::remember('stocks:dashboard:active_signals', now()->addSeconds(60), function () {
                return \App\Models\StockSignal::query()
                    ->with(['stock'])
                    ->active()
                    ->latest('signal_date')
                    ->limit(10)
                    ->get();
            });

            return response()->json([
                'success' => true,
                'watchlist' => $watchlist,
                'recentPredictions' => $recentPredictions,
                'activeSignals' => $activeSignals,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function stocksScreening(ScreenStocksRequest $request)
    {
        try {
            $filters = $request->validated();
            $user = $request->user();
            $page = max(1, (int) ($request->input('page', 1)));
            $perPage = min(max(1, (int) ($request->input('per_page', $filters['limit'] ?? 20))), 100);
            unset($filters['limit']);

            $cacheKey = 'stocks:gateway:screening:' . md5(json_encode([$filters, $user?->id, $page, $perPage]));
            $payload = Cache::remember($cacheKey, now()->addSeconds(60), function () use ($filters, $user, $page, $perPage) {
                $results = $this->screeningService->screen($filters, $user);
                $total = $results->count();
                $items = $results->slice(($page - 1) * $perPage, $perPage)->values();
                $data = $items->map(function ($stock) {
                    $latestPrice = $stock->getLatestPrice();
                    $latestIndicator = $stock->technicalIndicators()->latest('date')->first();
                    $latestSignal = $stock->signals()->active()->latest('signal_date')->first();
                    $latestPrediction = $stock->predictions()->latest('prediction_date')->first();
                    return [
                        'id' => $stock->id,
                        'code' => $stock->code,
                        'name' => $stock->name,
                        'sector' => $stock->sector,
                        'category' => $stock->category,
                        'current_price' => $latestPrice ? (float) $latestPrice->close : null,
                        'rsi' => $latestIndicator ? $latestIndicator->rsi : null,
                        'signal' => $latestSignal ? [
                            'type' => $latestSignal->signal_type,
                            'strength' => (float) $latestSignal->signal_strength,
                        ] : null,
                        'prediction' => $latestPrediction ? [
                            'predicted_price' => (float) $latestPrediction->predicted_price,
                            'confidence' => (float) $latestPrediction->confidence_score,
                        ] : null,
                    ];
                })->values();
                return ['data' => $data, 'total' => $total];
            });

            return response()->json([
                'success' => true,
                'data' => collect($payload['data']),
                'count' => $payload['total'],
                'pagination' => ['page' => $page, 'per_page' => $perPage],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function stocksWatchlist(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }
            $perPage = (int) $request->input('per_page', 20);
            $watchlist = \App\Models\StockWatchlist::where('user_id', $user->id)
                ->with(['stock' => function ($q) {
                    $q->with(['prices' => function ($q) {
                        $q->latest('date')->limit(1);
                    }]);
                }])
                ->latest()
                ->paginate($perPage);
            return response()->json(['success' => true, 'watchlist' => $watchlist]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function portfolioRecommendations(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }
            $recs = PortfolioRecommendation::where('user_id', $user->id)
                ->latest('generated_at')
                ->paginate((int) $request->input('per_page', 10));
            return response()->json(['success' => true, 'recommendations' => $recs]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
