<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToWatchlistRequest;
use App\Models\Stock;
use App\Models\StockWatchlist;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockWatchlistController extends Controller
{
    /**
     * Get user's watchlist.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $watchlist = StockWatchlist::where('user_id', $user->id)
            ->with(['stock' => function ($q) {
                $q->with(['prices' => function ($q) {
                    $q->latest('date')->limit(1);
                }]);
            }])
            ->latest()
            ->get();

        return Inertia::render('Stocks/Watchlist', [
            'watchlist' => $watchlist,
        ]);
    }

    /**
     * Add stock to watchlist.
     */
    public function store(AddToWatchlistRequest $request)
    {
        $user = $request->user();

        // Check if already in watchlist
        $existing = StockWatchlist::where('user_id', $user->id)
            ->where('stock_id', $request->stock_id)
            ->first();

        if ($existing) {
            return redirect()->back()->withErrors([
                'message' => 'Stock is already in your watchlist.',
            ]);
        }

        // Check watchlist size limit (free tier: 10, premium: unlimited)
        $watchlistCount = StockWatchlist::where('user_id', $user->id)->count();
        $limit = $this->getWatchlistLimit($user);

        if ($limit > 0 && $watchlistCount >= $limit) {
            return redirect()->back()->withErrors([
                'message' => "Watchlist limit reached ({$limit} stocks). Upgrade to premium for unlimited watchlist.",
            ]);
        }

        StockWatchlist::create([
            'user_id' => $user->id,
            'stock_id' => $request->stock_id,
            'notes' => $request->notes,
            'alert_price_above' => $request->alert_price_above,
            'alert_price_below' => $request->alert_price_below,
            'notify_on_signal' => $request->boolean('notify_on_signal', true),
        ]);

        return redirect()->back()->with('success', 'Stock added to watchlist.');
    }

    /**
     * Update watchlist item.
     */
    public function update(StockWatchlist $watchlist, Request $request)
    {
        $user = $request->user();

        // Ensure user owns this watchlist item
        if ($watchlist->user_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
            'alert_price_above' => ['nullable', 'numeric', 'min:0'],
            'alert_price_below' => ['nullable', 'numeric', 'min:0'],
            'notify_on_signal' => ['boolean'],
        ]);

        $watchlist->update($request->only([
            'notes',
            'alert_price_above',
            'alert_price_below',
            'notify_on_signal',
        ]));

        return redirect()->back()->with('success', 'Watchlist item updated.');
    }

    /**
     * Remove stock from watchlist.
     */
    public function destroy(StockWatchlist $watchlist)
    {
        $user = auth()->user();

        // Ensure user owns this watchlist item
        if ($watchlist->user_id !== $user->id) {
            abort(403);
        }

        $watchlist->delete();

        return redirect()->back()->with('success', 'Stock removed from watchlist.');
    }

    /**
     * Get watchlist limit for user.
     */
    protected function getWatchlistLimit($user): int
    {
        // Check if user is premium (implement your logic)
        $isPremium = $this->isPremiumUser($user);

        if ($isPremium) {
            return -1; // Unlimited
        }

        return config('stocks.free_tier_limits.watchlist_size', 10);
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

