<?php

namespace App\Services;

use App\Models\User;
use App\Models\Stock;
use App\Models\PortfolioRecommendation;
use App\Models\StockPrice;
use App\Models\StockPrediction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PortfolioRecommendationService
{
    protected const RISK_FREE_RATE = 0.05; // 5% annual risk-free rate

    /**
     * Generate portfolio recommendation for a user.
     *
     * @param User $user
     * @param string $riskProfile
     * @param float $investmentAmount
     * @param int $horizon
     * @return PortfolioRecommendation
     */
    public function generateRecommendation(
        User $user,
        string $riskProfile,
        float $investmentAmount,
        int $horizon
    ): PortfolioRecommendation {
        // Get suitable stocks for risk profile
        $stocks = $this->getStocksForRiskProfile($riskProfile);

        if ($stocks->isEmpty()) {
            throw new \Exception('No suitable stocks found for risk profile: ' . $riskProfile);
        }

        // Calculate optimal allocation
        $allocation = $this->calculateOptimalAllocation($stocks, $riskProfile, $investmentAmount);

        // Calculate portfolio metrics
        $expectedReturn = $this->calculateExpectedReturn($allocation);
        $expectedRisk = $this->calculatePortfolioRisk($allocation);
        $sharpeRatio = $this->calculateSharpeRatio($expectedReturn, $expectedRisk);

        // Create recommendation
        $recommendation = PortfolioRecommendation::create([
            'user_id' => $user->id,
            'risk_profile' => $riskProfile,
            'investment_amount' => $investmentAmount,
            'investment_horizon' => $horizon,
            'allocation' => $allocation,
            'expected_return' => $expectedReturn,
            'expected_risk' => $expectedRisk,
            'sharpe_ratio' => $sharpeRatio,
            'generated_at' => Carbon::now(),
        ]);

        Log::info('Portfolio recommendation generated', [
            'user_id' => $user->id,
            'risk_profile' => $riskProfile,
            'investment_amount' => $investmentAmount,
            'stocks_count' => count($allocation),
        ]);

        return $recommendation;
    }

    /**
     * Calculate optimal allocation using Modern Portfolio Theory.
     *
     * @param Collection $stocks
     * @param string $riskProfile
     * @param float $amount
     * @return array
     */
    public function calculateOptimalAllocation(Collection $stocks, string $riskProfile, float $amount): array
    {
        $allocation = [];
        $stockCount = $stocks->count();

        // Get expected returns and risks for each stock
        $stockMetrics = [];
        foreach ($stocks as $stock) {
            $metrics = $this->getStockMetrics($stock);
            if ($metrics) {
                $stockMetrics[$stock->id] = $metrics;
            }
        }

        if (empty($stockMetrics)) {
            // Fallback: equal allocation
            $equalPercentage = 100 / $stockCount;
            foreach ($stocks as $stock) {
                $allocation[$stock->id] = $equalPercentage;
            }
            return $allocation;
        }

        // Risk profile determines allocation strategy
        switch ($riskProfile) {
            case 'conservative':
                $allocation = $this->calculateConservativeAllocation($stockMetrics, $amount);
                break;
            case 'moderate':
                $allocation = $this->calculateModerateAllocation($stockMetrics, $amount);
                break;
            case 'aggressive':
                $allocation = $this->calculateAggressiveAllocation($stockMetrics, $amount);
                break;
            default:
                $allocation = $this->calculateModerateAllocation($stockMetrics, $amount);
        }

        // Normalize to ensure total is 100%
        $total = array_sum($allocation);
        if ($total > 0) {
            foreach ($allocation as $stockId => $percentage) {
                $allocation[$stockId] = ($percentage / $total) * 100;
            }
        }

        return $allocation;
    }

    /**
     * Calculate conservative allocation (low risk, stable stocks).
     */
    protected function calculateConservativeAllocation(array $stockMetrics, float $amount): array
    {
        $allocation = [];
        
        // Sort by risk (ascending) and return (descending)
        uasort($stockMetrics, function ($a, $b) {
            $riskDiff = $a['risk'] <=> $b['risk'];
            if ($riskDiff !== 0) {
                return $riskDiff; // Lower risk first
            }
            return $b['return'] <=> $a['return']; // Higher return first
        });

        // Allocate more to lower risk stocks
        $topStocks = array_slice($stockMetrics, 0, min(5, count($stockMetrics)), true);
        $count = count($topStocks);
        
        foreach ($topStocks as $stockId => $metrics) {
            // Weighted allocation favoring lower risk
            $weight = (1 / ($metrics['risk'] + 0.01)) * 100; // Inverse risk weighting
            $allocation[$stockId] = $weight;
        }

        return $allocation;
    }

    /**
     * Calculate moderate allocation (balanced).
     */
    protected function calculateModerateAllocation(array $stockMetrics, float $amount): array
    {
        $allocation = [];
        
        // Sort by Sharpe ratio (descending)
        uasort($stockMetrics, function ($a, $b) {
            $sharpeA = $this->calculateSharpeRatio($a['return'], $a['risk']);
            $sharpeB = $this->calculateSharpeRatio($b['return'], $b['risk']);
            return $sharpeB <=> $sharpeA;
        });

        // Allocate to top stocks with good risk-return ratio
        $topStocks = array_slice($stockMetrics, 0, min(8, count($stockMetrics)), true);
        $count = count($topStocks);
        
        foreach ($topStocks as $stockId => $metrics) {
            $sharpe = $this->calculateSharpeRatio($metrics['return'], $metrics['risk']);
            $weight = max(0.1, $sharpe * 20); // Weight by Sharpe ratio
            $allocation[$stockId] = $weight;
        }

        return $allocation;
    }

    /**
     * Calculate aggressive allocation (high risk, high return).
     */
    protected function calculateAggressiveAllocation(array $stockMetrics, float $amount): array
    {
        $allocation = [];
        
        // Sort by expected return (descending)
        uasort($stockMetrics, function ($a, $b) {
            return $b['return'] <=> $a['return'];
        });

        // Allocate more to higher return stocks
        $topStocks = array_slice($stockMetrics, 0, min(10, count($stockMetrics)), true);
        
        foreach ($topStocks as $stockId => $metrics) {
            // Weight by return potential
            $weight = $metrics['return'] * 10;
            $allocation[$stockId] = $weight;
        }

        return $allocation;
    }

    /**
     * Get stock metrics (expected return and risk).
     *
     * @param Stock $stock
     * @return array|null
     */
    protected function getStockMetrics(Stock $stock): ?array
    {
        // Get latest prediction for 30-day horizon
        $prediction = $stock->predictions()
            ->where('prediction_horizon', 30)
            ->where('prediction_date', '>=', Carbon::today()->subDays(7))
            ->latest('prediction_date')
            ->first();

        if (!$prediction) {
            return null;
        }

        // Get historical volatility
        $latestIndicator = $stock->technicalIndicators()->latest('date')->first();
        $volatility = $latestIndicator?->volatility ?? 0.03; // Default 3%

        // Calculate expected return from prediction
        $latestPrice = $stock->getLatestPrice();
        if (!$latestPrice) {
            return null;
        }

        $currentPrice = $latestPrice->close;
        $predictedPrice = $prediction->predicted_price;
        $expectedReturn = (($predictedPrice - $currentPrice) / $currentPrice) * (365 / 30); // Annualized

        return [
            'return' => max(0, $expectedReturn), // Ensure non-negative for calculation
            'risk' => $volatility,
            'confidence' => $prediction->confidence_score,
        ];
    }

    /**
     * Calculate expected return for portfolio.
     *
     * @param array $allocations
     * @return float
     */
    public function calculateExpectedReturn(array $allocations): float
    {
        $totalReturn = 0.0;

        foreach ($allocations as $stockId => $percentage) {
            $stock = Stock::find($stockId);
            if (!$stock) {
                continue;
            }

            $metrics = $this->getStockMetrics($stock);
            if ($metrics) {
                $totalReturn += ($metrics['return'] * $percentage) / 100;
            }
        }

        return $totalReturn;
    }

    /**
     * Calculate portfolio risk (volatility).
     *
     * @param array $allocations
     * @return float
     */
    public function calculatePortfolioRisk(array $allocations): float
    {
        // Simplified portfolio risk calculation
        // In a full implementation, we would calculate covariance matrix
        $weightedRisk = 0.0;

        foreach ($allocations as $stockId => $percentage) {
            $stock = Stock::find($stockId);
            if (!$stock) {
                continue;
            }

            $metrics = $this->getStockMetrics($stock);
            if ($metrics) {
                $weightedRisk += ($metrics['risk'] * $percentage) / 100;
            }
        }

        // Apply diversification benefit (simplified)
        $diversificationFactor = 1 - (0.3 / count($allocations)); // More stocks = less risk
        $portfolioRisk = $weightedRisk * max(0.5, $diversificationFactor);

        return $portfolioRisk;
    }

    /**
     * Calculate Sharpe ratio.
     *
     * @param float $return
     * @param float $risk
     * @param float $riskFreeRate
     * @return float
     */
    public function calculateSharpeRatio(float $return, float $risk, float $riskFreeRate = null): float
    {
        $riskFreeRate = $riskFreeRate ?? self::RISK_FREE_RATE;

        if ($risk == 0) {
            return 0.0;
        }

        return ($return - $riskFreeRate) / $risk;
    }

    /**
     * Get stocks suitable for risk profile.
     *
     * @param string $profile
     * @return Collection
     */
    public function getStocksForRiskProfile(string $profile): Collection
    {
        $query = Stock::active()
            ->whereHas('prices')
            ->whereHas('predictions')
            ->with(['prices', 'predictions', 'technicalIndicators']);

        switch ($profile) {
            case 'conservative':
                // Low volatility, stable stocks, large cap
                return $query->whereIn('category', ['LQ45', 'IDX30'])
                    ->whereHas('technicalIndicators', function ($q) {
                        $q->where('volatility', '<', 0.03)
                          ->latest('date');
                    })
                    ->limit(20)
                    ->get();

            case 'moderate':
                // Balanced mix
                return $query->whereIn('category', ['LQ45', 'IDX30', 'IDX80'])
                    ->limit(30)
                    ->get();

            case 'aggressive':
                // Higher volatility, growth potential
                return $query->whereHas('technicalIndicators', function ($q) {
                        $q->where('volatility', '>', 0.03)
                          ->latest('date');
                    })
                    ->whereHas('predictions', function ($q) {
                        $q->where('confidence_score', '>=', 0.6)
                          ->where('prediction_horizon', 30);
                    })
                    ->limit(40)
                    ->get();

            default:
                return $query->limit(20)->get();
        }
    }

    /**
     * Validate portfolio recommendation.
     *
     * @param PortfolioRecommendation $recommendation
     * @return array Validation errors (empty if valid)
     */
    public function validateRecommendation(PortfolioRecommendation $recommendation): array
    {
        $errors = [];

        $allocation = $recommendation->allocation ?? [];
        $total = array_sum($allocation);

        // Check total allocation is 100%
        if (abs($total - 100) > 0.01) {
            $errors[] = "Total allocation must be 100%, got {$total}%";
        }

        // Check minimum investment per stock
        $minInvestment = 100000; // 100k IDR minimum
        foreach ($allocation as $stockId => $percentage) {
            $amount = ($recommendation->investment_amount * $percentage) / 100;
            if ($amount < $minInvestment) {
                $stock = Stock::find($stockId);
                $errors[] = "Investment in {$stock?->code} ({$amount} IDR) is below minimum ({$minInvestment} IDR)";
            }
        }

        // Check diversification (at least 3 stocks)
        if (count($allocation) < 3) {
            $errors[] = "Portfolio must contain at least 3 stocks for diversification";
        }

        // Check no single stock exceeds 40% (unless aggressive)
        if ($recommendation->risk_profile !== 'aggressive') {
            foreach ($allocation as $stockId => $percentage) {
                if ($percentage > 40) {
                    $stock = Stock::find($stockId);
                    $errors[] = "Stock {$stock?->code} allocation ({$percentage}%) exceeds 40% limit";
                }
            }
        }

        return $errors;
    }
}

