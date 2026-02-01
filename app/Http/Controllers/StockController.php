<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\StockPrice;
use App\Models\StockTechnicalIndicator;
use App\Models\StockPrediction;
use App\Models\StockSignal;
use App\Models\StockWatchlist;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class StockController extends Controller
{
    /**
     * Show stock dashboard.
     */
    public function dashboard(Request $request): Response
    {
        $user = $request->user();
        $perPage = (int) $request->input('per_page', 10);
        $page = (int) $request->input('page', 1);
        // Get user watchlist
        $watchlist = collect([]);
        if ($user) {
            $watchlist = $user->stockWatchlists()
                ->with(['stock.prices' => function ($q) {
                    $q->latest('date')->limit(1);
                }])
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);
        }
        
        // Get recent predictions (cache 60s)
        $recentPredictions = \Illuminate\Support\Facades\Cache::remember('stocks:dashboard:recent_predictions', now()->addSeconds(60), function () {
            return StockPrediction::query()
                ->with(['stock', 'mlModel'])
                ->latest('prediction_date')
                ->limit(5)
                ->get();
        });
        
        // Get active signals (cache 60s)
        $activeSignals = \Illuminate\Support\Facades\Cache::remember('stocks:dashboard:active_signals', now()->addSeconds(60), function () {
            return StockSignal::query()
                ->with(['stock'])
                ->active()
                ->latest('signal_date')
                ->limit(10)
                ->get();
        });
        
        return Inertia::render('Stocks/StockDashboard', [
            'watchlist' => $watchlist,
            'recentPredictions' => $recentPredictions,
            'activeSignals' => $activeSignals,
        ]);
    }

    /**
     * List stocks with filters.
     */
    public function index(Request $request)
    {
        $query = Stock::query()->with(['prices' => function ($q) {
            $q->latest('date')->limit(1);
        }]);

        // Apply filters
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('sector')) {
            $query->where('sector', $request->sector);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        } else {
            $query->where('is_active', true);
        }

        $stocks = $query->paginate(20);

        if ($request->wantsJson()) {
            return response()->json($stocks);
        }

        return Inertia::render('Stocks/Index', [
            'stocks' => $stocks,
            'filters' => $request->only(['category', 'sector', 'search', 'active']),
        ]);
    }

    /**
     * Show stock details.
     */
    public function show(Request $request, string $stock): Response
    {
        $stockModel = Stock::where('id', $stock)->orWhere('code', $stock)->firstOrFail();
        $stockModel->load(['prices', 'technicalIndicators', 'signals', 'predictions']);

        $latestPrice = $stockModel->getLatestPrice();
        $latestIndicator = $stockModel->technicalIndicators()->latest('date')->first();
        $latestSignals = $stockModel->signals()->active()->latest()->limit(5)->get();
        $latestPredictions = $stockModel->predictions()
            ->where('prediction_date', '>=', Carbon::today()->subDays(7))
            ->latest('target_date')
            ->limit(10)
            ->get();

        return Inertia::render('Stocks/Show', [
            'stock' => $stockModel,
            'latestPrice' => $latestPrice,
            'latestIndicator' => $latestIndicator,
            'latestSignals' => $latestSignals,
            'latestPredictions' => $latestPredictions,
        ]);
    }

    /**
     * Get price history.
     */
    public function prices(Request $request, string $stock)
    {
        $stockModel = Stock::where('id', $stock)->orWhere('code', $stock)->firstOrFail();
        $query = $stockModel->prices()->where('is_intraday', false);

        if ($request->has('from')) {
            $query->where('date', '>=', $request->from);
        }

        if ($request->has('to')) {
            $query->where('date', '<=', $request->to);
        }

        if ($request->has('limit')) {
            $query->limit($request->limit);
        }

        $prices = $query->orderBy('date', 'asc')->get();

        return response()->json($prices);
    }

    /**
     * Get technical indicators.
     */
    public function indicators(Request $request, string $stock)
    {
        $stockModel = Stock::where('id', $stock)->orWhere('code', $stock)->firstOrFail();
        $query = $stockModel->technicalIndicators();

        if ($request->has('from')) {
            $query->where('date', '>=', $request->from);
        }

        if ($request->has('to')) {
            $query->where('date', '<=', $request->to);
        }

        if ($request->has('limit')) {
            $query->limit($request->limit);
        }

        $indicators = $query->orderBy('date', 'asc')->get();

        return response()->json($indicators);
    }

    /**
     * Get ML predictions (requires auth).
     */
    public function predictions(Request $request, string $stock)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $stockModel = Stock::where('id', $stock)->orWhere('code', $stock)->firstOrFail();
        $query = $stockModel->predictions()->with('mlModel');

        if ($request->has('horizon')) {
            $query->where('prediction_horizon', $request->horizon);
        }

        if ($request->has('from')) {
            $query->where('prediction_date', '>=', $request->from);
        }

        $predictions = $query->latest('prediction_date')->paginate(20);

        return response()->json($predictions);
    }

    /**
     * Get buy/sell signals.
     */
    public function signals(Request $request, string $stock)
    {
        $stockModel = Stock::where('id', $stock)->orWhere('code', $stock)->firstOrFail();
        $query = $stockModel->signals()->with('mlModel');

        if ($request->has('type')) {
            $query->where('signal_type', $request->type);
        }

        if ($request->has('active')) {
            if ($request->boolean('active')) {
                $query->active();
            } else {
                $query->expired();
            }
        } else {
            $query->active();
        }

        $signals = $query->latest('signal_date')->paginate(20);

        return response()->json($signals);
    }
}

