<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockSignal;
use App\Models\StockPrediction;
use App\Models\StockTechnicalIndicator;
use App\Models\StockWatchlist;
use App\Models\User;
use App\Events\StockSignalGenerated;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StockSignalService
{
    /**
     * Generate signals for a stock.
     *
     * @param Stock $stock
     * @param string $source
     * @return Collection
     */
    public function generateSignals(Stock $stock, string $source = 'ensemble'): Collection
    {
        $signals = collect();

        if ($source === 'ml_prediction' || $source === 'ensemble') {
            $mlSignals = $this->generateMLSignals($stock);
            $signals = $signals->merge($mlSignals);
        }

        if ($source === 'technical_analysis' || $source === 'ensemble') {
            $technicalSignals = $this->generateTechnicalSignals($stock);
            $signals = $signals->merge($technicalSignals);
        }

        if ($source === 'ensemble') {
            $ensembleSignal = $this->combineMLAndTechnicalSignals($stock);
            if ($ensembleSignal) {
                $signals->push($ensembleSignal);
            }
        }

        return $signals;
    }

    /**
     * Generate signals from ML predictions.
     *
     * @param Stock $stock
     * @return Collection
     */
    protected function generateMLSignals(Stock $stock): Collection
    {
        $signals = collect();
        $latestPrice = $stock->getLatestPrice();

        if (!$latestPrice) {
            return $signals;
        }

        // Get latest predictions for different horizons
        $predictions = $stock->predictions()
            ->where('prediction_date', Carbon::today())
            ->whereIn('prediction_horizon', [1, 7, 30])
            ->where('confidence_score', '>=', 0.6) // Minimum confidence
            ->latest('target_date')
            ->get();

        foreach ($predictions as $prediction) {
            $currentPrice = $latestPrice->close;
            $predictedPrice = $prediction->predicted_price;
            $priceChange = (($predictedPrice - $currentPrice) / $currentPrice) * 100;

            // Determine signal type based on price change
            $signalType = 'hold';
            if ($priceChange > 2) { // More than 2% increase
                $signalType = 'buy';
            } elseif ($priceChange < -2) { // More than 2% decrease
                $signalType = 'sell';
            }

            if ($signalType === 'hold') {
                continue;
            }

            // Calculate signal strength based on confidence and price change magnitude
            $signalStrength = min(1.0, abs($priceChange) / 10 * $prediction->confidence_score);

            // Calculate risk level
            $riskLevel = $this->calculateRiskLevelFromPrediction($stock, $prediction, $signalStrength);

            $signal = StockSignal::create([
                'stock_id' => $stock->id,
                'signal_type' => $signalType,
                'signal_strength' => $signalStrength,
                'signal_date' => Carbon::today(),
                'source' => 'ml_prediction',
                'ml_model_id' => $prediction->ml_model_id,
                'reason' => sprintf(
                    'ML prediction suggests %s%% change in %d days with %.1f%% confidence',
                    number_format($priceChange, 2),
                    $prediction->prediction_horizon,
                    $prediction->confidence_score * 100
                ),
                'price_target' => $predictedPrice,
                'risk_level' => $riskLevel,
                'expires_at' => Carbon::today()->addDays($prediction->prediction_horizon),
            ]);

            $this->setPriceTargets($stock, $signal);
            $signals->push($signal);

            // Dispatch event
            event(new StockSignalGenerated($signal));
        }

        return $signals;
    }

    /**
     * Generate signals from technical analysis.
     *
     * @param Stock $stock
     * @return Collection
     */
    protected function generateTechnicalSignals(Stock $stock): Collection
    {
        $signals = collect();
        $latestIndicator = $stock->technicalIndicators()
            ->latest('date')
            ->first();

        if (!$latestIndicator) {
            return $signals;
        }

        $signalStrength = $latestIndicator->getSignalStrength();
        $trend = $latestIndicator->getTrend();

        if (abs($signalStrength) < 0.3) {
            return $signals; // Signal too weak
        }

        $signalType = $signalStrength > 0 ? 'buy' : 'sell';
        $technicalIndicators = [];

        // Collect indicator data
        if ($latestIndicator->rsi !== null) {
            $technicalIndicators['rsi'] = $latestIndicator->rsi;
        }
        if ($latestIndicator->macd !== null && $latestIndicator->macd_signal !== null) {
            $technicalIndicators['macd'] = [
                'value' => $latestIndicator->macd,
                'signal' => $latestIndicator->macd_signal,
                'histogram' => $latestIndicator->macd_histogram,
            ];
        }

        $reason = $this->buildTechnicalReason($latestIndicator, $signalType);

        $signal = StockSignal::create([
            'stock_id' => $stock->id,
            'signal_type' => $signalType,
            'signal_strength' => abs($signalStrength),
            'signal_date' => Carbon::today(),
            'source' => 'technical_analysis',
            'technical_indicators' => $technicalIndicators,
            'reason' => $reason,
            'risk_level' => $this->calculateRiskLevelFromTechnical($stock, $latestIndicator),
            'expires_at' => Carbon::today()->addDays(7), // Technical signals expire in 7 days
        ]);

        $this->setPriceTargets($stock, $signal);
        $signals->push($signal);

        // Dispatch event
        event(new StockSignalGenerated($signal));

        return $signals;
    }

    /**
     * Combine ML and technical signals into ensemble signal.
     *
     * @param Stock $stock
     * @return StockSignal|null
     */
    public function combineMLAndTechnicalSignals(Stock $stock): ?StockSignal
    {
        $mlSignals = $this->generateMLSignals($stock);
        $technicalSignals = $this->generateTechnicalSignals($stock);

        if ($mlSignals->isEmpty() && $technicalSignals->isEmpty()) {
            return null;
        }

        // Get the strongest signals from each source
        $strongestML = $mlSignals->sortByDesc('signal_strength')->first();
        $strongestTechnical = $technicalSignals->sortByDesc('signal_strength')->first();

        $signals = collect([$strongestML, $strongestTechnical])->filter();

        if ($signals->isEmpty()) {
            return null;
        }

        // Calculate combined signal strength
        $combinedStrength = $this->calculateSignalStrength($signals->toArray());

        // Determine signal type (majority vote)
        $buyCount = $signals->where('signal_type', 'buy')->count();
        $sellCount = $signals->where('signal_type', 'sell')->count();

        $signalType = 'hold';
        if ($buyCount > $sellCount && $combinedStrength >= 0.5) {
            $signalType = 'buy';
        } elseif ($sellCount > $buyCount && $combinedStrength >= 0.5) {
            $signalType = 'sell';
        }

        if ($signalType === 'hold') {
            return null;
        }

        // Combine reasons
        $reasons = $signals->pluck('reason')->filter()->implode(' | ');

        $signal = StockSignal::create([
            'stock_id' => $stock->id,
            'signal_type' => $signalType,
            'signal_strength' => $combinedStrength,
            'signal_date' => Carbon::today(),
            'source' => 'ensemble',
            'ml_model_id' => $strongestML?->ml_model_id,
            'technical_indicators' => $strongestTechnical?->technical_indicators,
            'reason' => 'Ensemble: ' . $reasons,
            'risk_level' => $this->calculateRiskLevelFromPrediction($stock, null, $combinedStrength),
            'expires_at' => Carbon::today()->addDays(7),
        ]);

        $this->setPriceTargets($stock, $signal);

        // Dispatch event
        event(new StockSignalGenerated($signal));

        return $signal;
    }

    /**
     * Calculate signal strength from multiple signals.
     *
     * @param array $signals
     * @return float
     */
    public function calculateSignalStrength(array $signals): float
    {
        if (empty($signals)) {
            return 0.0;
        }

        $totalStrength = 0.0;
        $count = 0;

        foreach ($signals as $signal) {
            if ($signal instanceof StockSignal) {
                $totalStrength += $signal->signal_strength;
                $count++;
            }
        }

        if ($count === 0) {
            return 0.0;
        }

        // Average with bonus for agreement
        $average = $totalStrength / $count;

        // Check if signals agree
        $types = array_map(fn($s) => $s->signal_type, array_filter($signals, fn($s) => $s instanceof StockSignal));
        $uniqueTypes = array_unique($types);

        if (count($uniqueTypes) === 1 && count($types) > 1) {
            // All signals agree, boost strength
            $average = min(1.0, $average * 1.2);
        }

        return $average;
    }

    /**
     * Calculate risk level for a signal from prediction.
     *
     * @param Stock $stock
     * @param StockPrediction|null $prediction
     * @param float $signalStrength
     * @return string
     */
    protected function calculateRiskLevelFromPrediction(Stock $stock, ?StockPrediction $prediction, float $signalStrength): string
    {
        $latestIndicator = $stock->technicalIndicators()->latest('date')->first();
        $latestPrice = $stock->getLatestPrice();

        $riskFactors = 0;

        // Volatility risk
        if ($latestIndicator && $latestIndicator->volatility !== null) {
            if ($latestIndicator->volatility > 0.05) {
                $riskFactors += 2; // High volatility
            } elseif ($latestIndicator->volatility > 0.03) {
                $riskFactors += 1; // Medium volatility
            }
        }

        // Prediction confidence risk
        if ($prediction) {
            if ($prediction->confidence_score < 0.7) {
                $riskFactors += 1;
            }
        }

        // Signal strength risk
        if ($signalStrength < 0.5) {
            $riskFactors += 1;
        }

        // Determine risk level
        if ($riskFactors >= 3) {
            return 'very_high';
        } elseif ($riskFactors >= 2) {
            return 'high';
        } elseif ($riskFactors >= 1) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Calculate risk level from technical indicators.
     *
     * @param Stock $stock
     * @param StockTechnicalIndicator $indicator
     * @return string
     */
    protected function calculateRiskLevelFromTechnical(Stock $stock, StockTechnicalIndicator $indicator): string
    {
        $riskFactors = 0;

        if ($indicator->volatility !== null && $indicator->volatility > 0.05) {
            $riskFactors += 2;
        } elseif ($indicator->volatility !== null && $indicator->volatility > 0.03) {
            $riskFactors += 1;
        }

        if ($indicator->adx !== null && $indicator->adx < 25) {
            $riskFactors += 1; // Weak trend
        }

        if ($riskFactors >= 3) {
            return 'very_high';
        } elseif ($riskFactors >= 2) {
            return 'high';
        } elseif ($riskFactors >= 1) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Set price targets (stop loss, take profit) for a signal.
     *
     * @param Stock $stock
     * @param StockSignal $signal
     * @return void
     */
    public function setPriceTargets(Stock $stock, StockSignal $signal): void
    {
        $latestPrice = $stock->getLatestPrice();
        if (!$latestPrice) {
            return;
        }

        $currentPrice = $latestPrice->close;
        $latestIndicator = $stock->technicalIndicators()->latest('date')->first();

        // Use ATR for stop loss/take profit if available
        $atr = $latestIndicator?->atr ?? ($currentPrice * 0.02); // Default 2% if no ATR

        if ($signal->signal_type === 'buy') {
            // Stop loss: 2x ATR below entry
            $signal->stop_loss = max(0, $currentPrice - ($atr * 2));
            // Take profit: 3x ATR above entry
            $signal->take_profit = $currentPrice + ($atr * 3);
        } elseif ($signal->signal_type === 'sell') {
            // Stop loss: 2x ATR above entry
            $signal->stop_loss = $currentPrice + ($atr * 2);
            // Take profit: 3x ATR below entry
            $signal->take_profit = max(0, $currentPrice - ($atr * 3));
        }

        if ($signal->price_target === null) {
            $signal->price_target = $signal->take_profit;
        }

        $signal->save();
    }

    /**
     * Build technical analysis reason.
     *
     * @param StockTechnicalIndicator $indicator
     * @param string $signalType
     * @return string
     */
    protected function buildTechnicalReason(StockTechnicalIndicator $indicator, string $signalType): string
    {
        $reasons = [];

        if ($indicator->rsi !== null) {
            if ($indicator->rsi < 30) {
                $reasons[] = 'RSI oversold';
            } elseif ($indicator->rsi > 70) {
                $reasons[] = 'RSI overbought';
            }
        }

        if ($indicator->macd !== null && $indicator->macd_signal !== null) {
            if ($indicator->macd > $indicator->macd_signal && $indicator->macd_histogram > 0) {
                $reasons[] = 'MACD bullish crossover';
            } elseif ($indicator->macd < $indicator->macd_signal && $indicator->macd_histogram < 0) {
                $reasons[] = 'MACD bearish crossover';
            }
        }

        if ($indicator->sma_5 !== null && $indicator->sma_20 !== null) {
            if ($indicator->sma_5 > $indicator->sma_20) {
                $reasons[] = 'Short-term MA above long-term MA';
            } else {
                $reasons[] = 'Short-term MA below long-term MA';
            }
        }

        if (empty($reasons)) {
            return 'Technical indicators suggest ' . $signalType;
        }

        return implode(', ', $reasons);
    }

    /**
     * Expire old signals.
     *
     * @return int Number of expired signals
     */
    public function expireOldSignals(): int
    {
        return StockSignal::expired()
            ->where('expires_at', '<=', Carbon::now())
            ->update(['expires_at' => Carbon::now()]); // Mark as expired
    }

    /**
     * Notify watchlist users about new signal.
     *
     * @param StockSignal $signal
     * @return void
     */
    public function notifyWatchlistUsers(StockSignal $signal): void
    {
        $watchlists = StockWatchlist::where('stock_id', $signal->stock_id)
            ->where('notify_on_signal', true)
            ->with('user')
            ->get();

        foreach ($watchlists as $watchlist) {
            if ($watchlist->user) {
                // Send notification (implement notification service)
                Log::info('Signal notification for watchlist', [
                    'user_id' => $watchlist->user->id,
                    'stock_id' => $signal->stock_id,
                    'signal_type' => $signal->signal_type,
                ]);
            }
        }
    }
}

