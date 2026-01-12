<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\User;
use App\Models\StockScreening;
use App\Models\StockPrice;
use App\Models\StockTechnicalIndicator;
use App\Models\StockSignal;
use App\Models\StockPrediction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockScreeningService
{
    /**
     * Screen stocks with filters.
     *
     * @param array $filters
     * @param User|null $user
     * @return Collection
     */
    public function screen(array $filters, ?User $user = null): Collection
    {
        // Start with all active stocks
        $stocks = Stock::active()->with(['prices', 'technicalIndicators', 'signals', 'predictions']);

        // Apply all filters
        $stocks = $this->applyFilters($stocks, $filters);

        // Get results
        $results = $stocks->get();

        // Apply tiered access limits
        $results = $this->applyTieredAccess($results, $user);

        return $results;
    }

    /**
     * Apply filters to stock query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applyFilters($query, array $filters)
    {
        // Filter by sector
        if (isset($filters['sector']) && is_array($filters['sector']) && !empty($filters['sector'])) {
            $query = $this->filterBySector($query, $filters['sector']);
        }

        // Filter by category
        if (isset($filters['category']) && is_array($filters['category']) && !empty($filters['category'])) {
            $query = $this->filterByCategory($query, $filters['category']);
        }

        // Filter by price range
        if (isset($filters['price_min']) || isset($filters['price_max'])) {
            $query = $this->filterByPriceRange(
                $query,
                $filters['price_min'] ?? null,
                $filters['price_max'] ?? null
            );
        }

        // Filter by volume
        if (isset($filters['volume_min'])) {
            $query = $this->filterByVolume($query, $filters['volume_min']);
        }

        // Filter by RSI
        if (isset($filters['rsi_min']) || isset($filters['rsi_max'])) {
            $query = $this->filterByRSI(
                $query,
                $filters['rsi_min'] ?? null,
                $filters['rsi_max'] ?? null
            );
        }

        // Filter by MACD
        if (isset($filters['macd_bullish'])) {
            $query = $this->filterByMACD($query, (bool) $filters['macd_bullish']);
        }

        // Filter by signal type
        if (isset($filters['signal_type'])) {
            $query = $this->filterBySignal(
                $query,
                $filters['signal_type'],
                $filters['signal_strength_min'] ?? null
            );
        }

        // Filter by risk level
        if (isset($filters['risk_level']) && is_array($filters['risk_level']) && !empty($filters['risk_level'])) {
            $query = $this->filterByRiskLevel($query, $filters['risk_level']);
        }

        // Filter by prediction confidence
        if (isset($filters['prediction_confidence_min'])) {
            $query = $this->filterByPrediction(
                $query,
                $filters['prediction_confidence_min'],
                $filters['prediction_horizon'] ?? 1
            );
        }

        return $query;
    }

    /**
     * Filter stocks by price range.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float|null $min
     * @param float|null $max
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterByPriceRange($query, ?float $min, ?float $max)
    {
        if ($min === null && $max === null) {
            return $query;
        }

        return $query->whereExists(function ($subQuery) use ($min, $max) {
            $subQuery->select(\DB::raw(1))
                ->from('stock_prices as sp')
                ->whereColumn('sp.stock_id', 'stocks.id')
                ->where('sp.is_intraday', false)
                ->where('sp.date', function ($dateQuery) {
                    $dateQuery->select(\DB::raw('MAX(date)'))
                        ->from('stock_prices as sp2')
                        ->whereColumn('sp2.stock_id', 'stocks.id')
                        ->where('sp2.is_intraday', false);
                });
            
            if ($min !== null) {
                $subQuery->where('sp.close', '>=', $min);
            }
            if ($max !== null) {
                $subQuery->where('sp.close', '<=', $max);
            }
        });
    }

    /**
     * Filter stocks by volume.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $minVolume
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterByVolume($query, int $minVolume)
    {
        return $query->whereExists(function ($subQuery) use ($minVolume) {
            $subQuery->select(\DB::raw(1))
                ->from('stock_prices as sp')
                ->whereColumn('sp.stock_id', 'stocks.id')
                ->where('sp.is_intraday', false)
                ->where('sp.date', function ($dateQuery) {
                    $dateQuery->select(\DB::raw('MAX(date)'))
                        ->from('stock_prices as sp2')
                        ->whereColumn('sp2.stock_id', 'stocks.id')
                        ->where('sp2.is_intraday', false);
                })
                ->where('sp.volume', '>=', $minVolume);
        });
    }

    /**
     * Filter stocks by sector.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $sectors
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterBySector($query, array $sectors)
    {
        return $query->whereIn('sector', $sectors);
    }

    /**
     * Filter stocks by category.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $categories
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterByCategory($query, array $categories)
    {
        return $query->whereIn('category', $categories);
    }

    /**
     * Filter stocks by RSI range.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float|null $minRSI
     * @param float|null $maxRSI
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterByRSI($query, ?float $minRSI, ?float $maxRSI)
    {
        if ($minRSI === null && $maxRSI === null) {
            return $query;
        }

        return $query->whereExists(function ($subQuery) use ($minRSI, $maxRSI) {
            $subQuery->select(\DB::raw(1))
                ->from('stock_technical_indicators as sti')
                ->whereColumn('sti.stock_id', 'stocks.id')
                ->where('sti.date', function ($dateQuery) {
                    $dateQuery->select(\DB::raw('MAX(date)'))
                        ->from('stock_technical_indicators as sti2')
                        ->whereColumn('sti2.stock_id', 'stocks.id');
                })
                ->whereNotNull('sti.rsi');
            
            if ($minRSI !== null) {
                $subQuery->where('sti.rsi', '>=', $minRSI);
            }
            if ($maxRSI !== null) {
                $subQuery->where('sti.rsi', '<=', $maxRSI);
            }
        });
    }

    /**
     * Filter stocks by MACD bullish/bearish.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $bullish
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterByMACD($query, bool $bullish = true)
    {
        return $query->whereExists(function ($subQuery) use ($bullish) {
            $subQuery->select(\DB::raw(1))
                ->from('stock_technical_indicators as sti')
                ->whereColumn('sti.stock_id', 'stocks.id')
                ->where('sti.date', function ($dateQuery) {
                    $dateQuery->select(\DB::raw('MAX(date)'))
                        ->from('stock_technical_indicators as sti2')
                        ->whereColumn('sti2.stock_id', 'stocks.id');
                })
                ->whereNotNull('sti.macd')
                ->whereNotNull('sti.macd_signal');
            
            if ($bullish) {
                $subQuery->whereRaw('sti.macd > sti.macd_signal')
                  ->where('sti.macd_histogram', '>', 0);
            } else {
                $subQuery->whereRaw('sti.macd < sti.macd_signal')
                  ->where('sti.macd_histogram', '<', 0);
            }
        });
    }

    /**
     * Filter stocks by signal type and strength.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $signalType buy, sell, hold
     * @param float|null $minStrength
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterBySignal($query, string $signalType, ?float $minStrength = null)
    {
        return $query->whereExists(function ($subQuery) use ($signalType, $minStrength) {
            $subQuery->select(DB::raw(1))
                ->from('stock_signals as ss')
                ->whereColumn('ss.stock_id', 'stocks.id')
                ->where('ss.signal_type', $signalType)
                ->where(function ($q) {
                    $q->whereNull('ss.expires_at')
                      ->orWhere('ss.expires_at', '>', Carbon::now());
                })
                ->where('ss.signal_date', function ($dateQuery) use ($signalType) {
                    $dateQuery->select(DB::raw('MAX(signal_date)'))
                        ->from('stock_signals as ss2')
                        ->whereColumn('ss2.stock_id', 'stocks.id')
                        ->where('ss2.signal_type', $signalType)
                        ->where(function ($q) {
                            $q->whereNull('ss2.expires_at')
                              ->orWhere('ss2.expires_at', '>', Carbon::now());
                        });
                });
            
            if ($minStrength !== null) {
                $subQuery->where('ss.signal_strength', '>=', $minStrength);
            }
        });
    }

    /**
     * Filter stocks by risk level.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $riskLevels
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterByRiskLevel($query, array $riskLevels)
    {
        return $query->whereExists(function ($subQuery) use ($riskLevels) {
            $subQuery->select(DB::raw(1))
                ->from('stock_signals as ss')
                ->whereColumn('ss.stock_id', 'stocks.id')
                ->whereIn('ss.risk_level', $riskLevels)
                ->where(function ($q) {
                    $q->whereNull('ss.expires_at')
                      ->orWhere('ss.expires_at', '>', Carbon::now());
                })
                ->where('ss.signal_date', function ($dateQuery) use ($riskLevels) {
                    $dateQuery->select(DB::raw('MAX(signal_date)'))
                        ->from('stock_signals as ss2')
                        ->whereColumn('ss2.stock_id', 'stocks.id')
                        ->whereIn('ss2.risk_level', $riskLevels)
                        ->where(function ($q) {
                            $q->whereNull('ss2.expires_at')
                              ->orWhere('ss2.expires_at', '>', Carbon::now());
                        });
                });
        });
    }

    /**
     * Filter stocks by prediction confidence.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float|null $minConfidence
     * @param int $horizon
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterByPrediction($query, ?float $minConfidence, int $horizon = 1)
    {
        if ($minConfidence === null) {
            return $query;
        }

        return $query->whereExists(function ($subQuery) use ($minConfidence, $horizon) {
            $subQuery->select(DB::raw(1))
                ->from('stock_predictions as sp')
                ->whereColumn('sp.stock_id', 'stocks.id')
                ->where('sp.prediction_horizon', $horizon)
                ->where('sp.confidence_score', '>=', $minConfidence)
                ->where('sp.prediction_date', function ($dateQuery) use ($horizon) {
                    $dateQuery->select(DB::raw('MAX(prediction_date)'))
                        ->from('stock_predictions as sp2')
                        ->whereColumn('sp2.stock_id', 'stocks.id')
                        ->where('sp2.prediction_horizon', $horizon);
                });
        });
    }

    /**
     * Get free tier limits.
     *
     * @return array
     */
    public function getFreeTierLimits(): array
    {
        return config('stocks.free_tier_limits', [
            'screening_results' => 20,
            'predictions_per_day' => 10,
            'watchlist_size' => 10,
            'portfolio_recommendations' => false,
        ]);
    }

    /**
     * Get premium tier limits.
     *
     * @return array
     */
    public function getPremiumTierLimits(): array
    {
        return config('stocks.premium_tier_limits', [
            'screening_results' => 100,
            'predictions_per_day' => -1,
            'watchlist_size' => -1,
            'portfolio_recommendations' => true,
        ]);
    }

    /**
     * Check if user is premium.
     *
     * @param User|null $user
     * @return bool
     */
    protected function isPremiumUser(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        // Check if user has premium role or subscription
        // This can be extended based on your user model structure
        // For now, we'll check if user has a premium role
        if (isset($user->role) && in_array($user->role, ['premium', 'admin'])) {
            return true;
        }

        // You can also check for subscription status here
        // if ($user->hasActiveSubscription('premium')) {
        //     return true;
        // }

        return false;
    }

    /**
     * Apply tiered access limits to results.
     *
     * @param Collection $results
     * @param User|null $user
     * @return Collection
     */
    public function applyTieredAccess(Collection $results, ?User $user): Collection
    {
        $isPremium = $this->isPremiumUser($user);
        
        if ($isPremium) {
            $limits = $this->getPremiumTierLimits();
        } else {
            $limits = $this->getFreeTierLimits();
        }

        $maxResults = $limits['screening_results'];
        
        // -1 means unlimited
        if ($maxResults === -1) {
            return $results;
        }

        return $results->take($maxResults);
    }

    /**
     * Save screening criteria.
     *
     * @param array $filters
     * @param User|null $user
     * @param string|null $name
     * @return StockScreening
     */
    public function saveScreening(array $filters, ?User $user = null, ?string $name = null): StockScreening
    {
        return StockScreening::create([
            'user_id' => $user?->id,
            'name' => $name,
            'filters' => $filters,
            'is_saved' => true,
        ]);
    }

    /**
     * Run and save screening results.
     *
     * @param StockScreening $screening
     * @return StockScreening
     */
    public function runAndSaveResults(StockScreening $screening): StockScreening
    {
        $user = $screening->user_id ? User::find($screening->user_id) : null;
        
        // Run screening
        $results = $this->screen($screening->filters, $user);
        
        // Prepare results data
        $resultsData = $results->map(function ($stock) {
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
                'volume' => $latestPrice ? $latestPrice->volume : null,
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
                ] : null,
                'prediction' => $latestPrediction ? [
                    'predicted_price' => (float) $latestPrediction->predicted_price,
                    'confidence' => (float) $latestPrediction->confidence_score,
                    'horizon' => $latestPrediction->prediction_horizon,
                ] : null,
            ];
        })->toArray();
        
        // Update screening with results
        $screening->update([
            'results' => $resultsData,
            'results_count' => count($resultsData),
            'last_run_at' => Carbon::now(),
        ]);
        
        return $screening;
    }

    /**
     * Get saved screenings for a user.
     *
     * @param User $user
     * @return Collection
     */
    public function getSavedScreenings(User $user): Collection
    {
        return StockScreening::where('user_id', $user->id)
            ->where('is_saved', true)
            ->orderBy('updated_at', 'desc')
            ->get();
    }
}

