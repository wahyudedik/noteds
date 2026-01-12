<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockPrice;
use App\Models\StockTechnicalIndicator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TechnicalIndicatorService
{
    /**
     * Calculate Simple Moving Average (SMA).
     *
     * @param Collection<StockPrice> $prices
     * @param int $period
     * @return Collection
     */
    public function calculateSMA(Collection $prices, int $period): Collection
    {
        if ($prices->count() < $period) {
            return collect();
        }

        $sma = collect();
        $sortedPrices = $prices->sortBy('date')->values();

        for ($i = $period - 1; $i < $sortedPrices->count(); $i++) {
            $sum = 0;
            for ($j = $i - $period + 1; $j <= $i; $j++) {
                $sum += (float) $sortedPrices[$j]->close;
            }
            $sma->push([
                'date' => $sortedPrices[$i]->date,
                'value' => $sum / $period,
            ]);
        }

        return $sma;
    }

    /**
     * Calculate Exponential Moving Average (EMA).
     *
     * @param Collection<StockPrice> $prices
     * @param int $period
     * @return Collection
     */
    public function calculateEMA(Collection $prices, int $period): Collection
    {
        if ($prices->count() < $period) {
            return collect();
        }

        $ema = collect();
        $sortedPrices = $prices->sortBy('date')->values();
        $multiplier = 2 / ($period + 1);

        // First EMA value is SMA
        $sum = 0;
        for ($i = 0; $i < $period; $i++) {
            $sum += (float) $sortedPrices[$i]->close;
        }
        $previousEMA = $sum / $period;

        $ema->push([
            'date' => $sortedPrices[$period - 1]->date,
            'value' => $previousEMA,
        ]);

        // Calculate subsequent EMA values
        for ($i = $period; $i < $sortedPrices->count(); $i++) {
            $currentPrice = (float) $sortedPrices[$i]->close;
            $currentEMA = ($currentPrice - $previousEMA) * $multiplier + $previousEMA;
            
            $ema->push([
                'date' => $sortedPrices[$i]->date,
                'value' => $currentEMA,
            ]);

            $previousEMA = $currentEMA;
        }

        return $ema;
    }

    /**
     * Calculate Relative Strength Index (RSI).
     *
     * @param Collection<StockPrice> $prices
     * @param int $period
     * @return Collection
     */
    public function calculateRSI(Collection $prices, int $period = 14): Collection
    {
        if ($prices->count() < $period + 1) {
            return collect();
        }

        $rsi = collect();
        $sortedPrices = $prices->sortBy('date')->values();
        $gains = [];
        $losses = [];

        // Calculate price changes
        for ($i = 1; $i < $sortedPrices->count(); $i++) {
            $change = (float) $sortedPrices[$i]->close - (float) $sortedPrices[$i - 1]->close;
            $gains[] = $change > 0 ? $change : 0;
            $losses[] = $change < 0 ? abs($change) : 0;
        }

        // Calculate initial average gain and loss
        $avgGain = array_sum(array_slice($gains, 0, $period)) / $period;
        $avgLoss = array_sum(array_slice($losses, 0, $period)) / $period;

        // Calculate first RSI
        if ($avgLoss == 0) {
            $firstRSI = 100;
        } else {
            $rs = $avgGain / $avgLoss;
            $firstRSI = 100 - (100 / (1 + $rs));
        }

        $rsi->push([
            'date' => $sortedPrices[$period]->date,
            'value' => $firstRSI,
        ]);

        // Calculate subsequent RSI values using Wilder's smoothing
        for ($i = $period; $i < count($gains); $i++) {
            $avgGain = ($avgGain * ($period - 1) + $gains[$i]) / $period;
            $avgLoss = ($avgLoss * ($period - 1) + $losses[$i]) / $period;

            if ($avgLoss == 0) {
                $currentRSI = 100;
            } else {
                $rs = $avgGain / $avgLoss;
                $currentRSI = 100 - (100 / (1 + $rs));
            }

            $rsi->push([
                'date' => $sortedPrices[$i + 1]->date,
                'value' => $currentRSI,
            ]);
        }

        return $rsi;
    }

    /**
     * Calculate MACD (Moving Average Convergence Divergence).
     *
     * @param Collection<StockPrice> $prices
     * @param int $fast
     * @param int $slow
     * @param int $signal
     * @return Collection
     */
    public function calculateMACD(Collection $prices, int $fast = 12, int $slow = 26, int $signal = 9): Collection
    {
        if ($prices->count() < $slow + $signal) {
            return collect();
        }

        $macd = collect();
        $fastEMA = $this->calculateEMA($prices, $fast);
        $slowEMA = $this->calculateEMA($prices, $slow);

        if ($fastEMA->isEmpty() || $slowEMA->isEmpty()) {
            return collect();
        }

        // Align EMAs by date
        $fastEMAMap = $fastEMA->keyBy('date');
        $slowEMAMap = $slowEMA->keyBy('date');
        $commonDates = $fastEMAMap->keys()->intersect($slowEMAMap->keys())->sort();

        // Calculate MACD line (fast EMA - slow EMA)
        $macdLine = collect();
        foreach ($commonDates as $date) {
            $macdValue = $fastEMAMap[$date]['value'] - $slowEMAMap[$date]['value'];
            $macdLine->push([
                'date' => $date,
                'value' => $macdValue,
            ]);
        }

        // Calculate signal line (EMA of MACD line)
        $signalLine = $this->calculateEMAFromValues($macdLine, $signal);

        // Calculate histogram (MACD - Signal)
        $signalMap = $signalLine->keyBy('date');
        $macdDates = $macdLine->pluck('date');

        foreach ($macdDates as $date) {
            $macdValue = $macdLine->firstWhere('date', $date)['value'];
            $signalValue = $signalMap[$date]['value'] ?? 0;

            $macd->push([
                'date' => $date,
                'macd' => $macdValue,
                'signal' => $signalValue,
                'histogram' => $macdValue - $signalValue,
            ]);
        }

        return $macd;
    }

    /**
     * Calculate Bollinger Bands.
     *
     * @param Collection<StockPrice> $prices
     * @param int $period
     * @param float $stdDev
     * @return Collection
     */
    public function calculateBollingerBands(Collection $prices, int $period = 20, float $stdDev = 2): Collection
    {
        if ($prices->count() < $period) {
            return collect();
        }

        $bands = collect();
        $sortedPrices = $prices->sortBy('date')->values();
        $sma = $this->calculateSMA($prices, $period);
        $smaMap = $sma->keyBy('date');

        for ($i = $period - 1; $i < $sortedPrices->count(); $i++) {
            $date = $sortedPrices[$i]->date;
            $middle = $smaMap[$date]['value'] ?? null;

            if ($middle === null) {
                continue;
            }

            // Calculate standard deviation
            $sumSquaredDiff = 0;
            for ($j = $i - $period + 1; $j <= $i; $j++) {
                $diff = (float) $sortedPrices[$j]->close - $middle;
                $sumSquaredDiff += $diff * $diff;
            }
            $variance = $sumSquaredDiff / $period;
            $standardDeviation = sqrt($variance);

            $bands->push([
                'date' => $date,
                'middle' => $middle,
                'upper' => $middle + ($stdDev * $standardDeviation),
                'lower' => $middle - ($stdDev * $standardDeviation),
            ]);
        }

        return $bands;
    }

    /**
     * Calculate Stochastic Oscillator.
     *
     * @param Collection<StockPrice> $prices
     * @param int $kPeriod
     * @param int $dPeriod
     * @return Collection
     */
    public function calculateStochastic(Collection $prices, int $kPeriod = 14, int $dPeriod = 3): Collection
    {
        if ($prices->count() < $kPeriod + $dPeriod - 1) {
            return collect();
        }

        $stochastic = collect();
        $sortedPrices = $prices->sortBy('date')->values();

        // Calculate %K
        $kValues = collect();
        for ($i = $kPeriod - 1; $i < $sortedPrices->count(); $i++) {
            $highs = [];
            $lows = [];
            for ($j = $i - $kPeriod + 1; $j <= $i; $j++) {
                $highs[] = (float) $sortedPrices[$j]->high;
                $lows[] = (float) $sortedPrices[$j]->low;
            }

            $highestHigh = max($highs);
            $lowestLow = min($lows);
            $currentClose = (float) $sortedPrices[$i]->close;

            if ($highestHigh == $lowestLow) {
                $kValue = 50; // Neutral when no range
            } else {
                $kValue = (($currentClose - $lowestLow) / ($highestHigh - $lowestLow)) * 100;
            }

            $kValues->push([
                'date' => $sortedPrices[$i]->date,
                'value' => $kValue,
            ]);
        }

        // Calculate %D (SMA of %K)
        $dValues = collect();
        $kValuesArray = $kValues->values()->all();

        for ($i = $dPeriod - 1; $i < count($kValuesArray); $i++) {
            $sum = 0;
            for ($j = $i - $dPeriod + 1; $j <= $i; $j++) {
                $sum += $kValuesArray[$j]['value'];
            }
            $dValue = $sum / $dPeriod;

            $stochastic->push([
                'date' => $kValuesArray[$i]['date'],
                'k' => $kValuesArray[$i]['value'],
                'd' => $dValue,
            ]);
        }

        return $stochastic;
    }

    /**
     * Calculate Average Directional Index (ADX).
     *
     * @param Collection<StockPrice> $prices
     * @param int $period
     * @return Collection
     */
    public function calculateADX(Collection $prices, int $period = 14): Collection
    {
        if ($prices->count() < $period * 2) {
            return collect();
        }

        $adx = collect();
        $sortedPrices = $prices->sortBy('date')->values();

        // Calculate True Range (TR) and Directional Movement
        $trValues = [];
        $plusDM = [];
        $minusDM = [];

        for ($i = 1; $i < $sortedPrices->count(); $i++) {
            $high = (float) $sortedPrices[$i]->high;
            $low = (float) $sortedPrices[$i]->low;
            $prevClose = (float) $sortedPrices[$i - 1]->close;

            // True Range
            $tr = max(
                $high - $low,
                abs($high - $prevClose),
                abs($low - $prevClose)
            );
            $trValues[] = $tr;

            // Directional Movement
            $upMove = $high - (float) $sortedPrices[$i - 1]->high;
            $downMove = (float) $sortedPrices[$i - 1]->low - $low;

            if ($upMove > $downMove && $upMove > 0) {
                $plusDM[] = $upMove;
                $minusDM[] = 0;
            } elseif ($downMove > $upMove && $downMove > 0) {
                $plusDM[] = 0;
                $minusDM[] = $downMove;
            } else {
                $plusDM[] = 0;
                $minusDM[] = 0;
            }
        }

        // Calculate smoothed TR, +DM, -DM
        $smoothedTR = [];
        $smoothedPlusDM = [];
        $smoothedMinusDM = [];

        // Initial values (sum of first period)
        $smoothedTR[] = array_sum(array_slice($trValues, 0, $period));
        $smoothedPlusDM[] = array_sum(array_slice($plusDM, 0, $period));
        $smoothedMinusDM[] = array_sum(array_slice($minusDM, 0, $period));

        // Subsequent values (Wilder's smoothing)
        for ($i = $period; $i < count($trValues); $i++) {
            $smoothedTR[] = $smoothedTR[$i - $period] - ($smoothedTR[$i - $period] / $period) + $trValues[$i];
            $smoothedPlusDM[] = $smoothedPlusDM[$i - $period] - ($smoothedPlusDM[$i - $period] / $period) + $plusDM[$i];
            $smoothedMinusDM[] = $smoothedMinusDM[$i - $period] - ($smoothedMinusDM[$i - $period] / $period) + $minusDM[$i];
        }

        // Calculate +DI and -DI
        $plusDI = [];
        $minusDI = [];

        for ($i = 0; $i < count($smoothedTR); $i++) {
            if ($smoothedTR[$i] == 0) {
                $plusDI[] = 0;
                $minusDI[] = 0;
            } else {
                $plusDI[] = ($smoothedPlusDM[$i] / $smoothedTR[$i]) * 100;
                $minusDI[] = ($smoothedMinusDM[$i] / $smoothedTR[$i]) * 100;
            }
        }

        // Calculate DX
        $dx = [];
        for ($i = 0; $i < count($plusDI); $i++) {
            $diSum = $plusDI[$i] + $minusDI[$i];
            if ($diSum == 0) {
                $dx[] = 0;
            } else {
                $dx[] = (abs($plusDI[$i] - $minusDI[$i]) / $diSum) * 100;
            }
        }

        // Calculate ADX (smoothed DX)
        $adxValues = [];
        $adxValues[] = array_sum(array_slice($dx, 0, $period)) / $period;

        for ($i = $period; $i < count($dx); $i++) {
            $adxValues[] = ($adxValues[$i - $period] * ($period - 1) + $dx[$i]) / $period;
        }

        // Map ADX values to dates
        $dateIndex = $period * 2 - 1;
        foreach ($adxValues as $adxValue) {
            if ($dateIndex < $sortedPrices->count()) {
                $adx->push([
                    'date' => $sortedPrices[$dateIndex]->date,
                    'value' => $adxValue,
                ]);
                $dateIndex++;
            }
        }

        return $adx;
    }

    /**
     * Calculate Average True Range (ATR).
     *
     * @param Collection<StockPrice> $prices
     * @param int $period
     * @return Collection
     */
    public function calculateATR(Collection $prices, int $period = 14): Collection
    {
        if ($prices->count() < $period + 1) {
            return collect();
        }

        $atr = collect();
        $sortedPrices = $prices->sortBy('date')->values();

        // Calculate True Range
        $trValues = [];
        for ($i = 1; $i < $sortedPrices->count(); $i++) {
            $high = (float) $sortedPrices[$i]->high;
            $low = (float) $sortedPrices[$i]->low;
            $prevClose = (float) $sortedPrices[$i - 1]->close;

            $tr = max(
                $high - $low,
                abs($high - $prevClose),
                abs($low - $prevClose)
            );
            $trValues[] = $tr;
        }

        // Calculate ATR (smoothed TR using Wilder's method)
        $atrValues = [];
        $atrValues[] = array_sum(array_slice($trValues, 0, $period)) / $period;

        for ($i = $period; $i < count($trValues); $i++) {
            $atrValues[] = ($atrValues[$i - $period] * ($period - 1) + $trValues[$i]) / $period;
        }

        // Map ATR values to dates
        $dateIndex = $period;
        foreach ($atrValues as $atrValue) {
            if ($dateIndex < $sortedPrices->count()) {
                $atr->push([
                    'date' => $sortedPrices[$dateIndex]->date,
                    'value' => $atrValue,
                ]);
                $dateIndex++;
            }
        }

        return $atr;
    }

    /**
     * Calculate Volatility (standard deviation of returns).
     *
     * @param Collection<StockPrice> $prices
     * @param int $period
     * @return Collection
     */
    public function calculateVolatility(Collection $prices, int $period = 20): Collection
    {
        if ($prices->count() < $period + 1) {
            return collect();
        }

        $volatility = collect();
        $sortedPrices = $prices->sortBy('date')->values();

        // Calculate returns
        $returns = [];
        for ($i = 1; $i < $sortedPrices->count(); $i++) {
            $prevClose = (float) $sortedPrices[$i - 1]->close;
            $currentClose = (float) $sortedPrices[$i]->close;
            
            if ($prevClose == 0) {
                $returns[] = 0;
            } else {
                $returns[] = ($currentClose - $prevClose) / $prevClose;
            }
        }

        // Calculate rolling volatility (standard deviation of returns)
        for ($i = $period - 1; $i < count($returns); $i++) {
            $periodReturns = array_slice($returns, $i - $period + 1, $period);
            $mean = array_sum($periodReturns) / $period;
            
            $variance = 0;
            foreach ($periodReturns as $return) {
                $variance += pow($return - $mean, 2);
            }
            $variance = $variance / $period;
            $stdDev = sqrt($variance);

            $volatility->push([
                'date' => $sortedPrices[$i + 1]->date,
                'value' => $stdDev,
            ]);
        }

        return $volatility;
    }

    /**
     * Update all technical indicators for a stock.
     *
     * @param string $stockCode
     * @return void
     */
    public function updateIndicatorsForStock(string $stockCode): void
    {
        try {
            $stock = Stock::where('code', $stockCode)->first();

            if (!$stock) {
                Log::warning('Stock not found for indicator update', ['code' => $stockCode]);
                return;
            }

            // Get all prices (excluding intraday)
            $prices = $stock->prices()
                ->where('is_intraday', false)
                ->orderBy('date', 'asc')
                ->get();

            if ($prices->count() < 200) {
                Log::warning('Insufficient price data for indicator calculation', [
                    'code' => $stockCode,
                    'count' => $prices->count(),
                ]);
                return;
            }

            // Calculate all indicators
            $config = config('stocks.technical_indicators', []);

            // SMA
            $smaPeriods = $config['sma_periods'] ?? [5, 10, 20, 50, 200];
            $smaData = [];
            foreach ($smaPeriods as $period) {
                $sma = $this->calculateSMA($prices, $period);
                $smaData[$period] = $sma->keyBy('date');
            }

            // EMA
            $emaPeriods = $config['ema_periods'] ?? [12, 26];
            $emaData = [];
            foreach ($emaPeriods as $period) {
                $ema = $this->calculateEMA($prices, $period);
                $emaData[$period] = $ema->keyBy('date');
            }

            // RSI
            $rsiPeriod = $config['rsi_period'] ?? 14;
            $rsiData = $this->calculateRSI($prices, $rsiPeriod)->keyBy('date');

            // MACD
            $macdConfig = $config['macd'] ?? ['fast' => 12, 'slow' => 26, 'signal' => 9];
            $macdData = $this->calculateMACD(
                $prices,
                $macdConfig['fast'],
                $macdConfig['slow'],
                $macdConfig['signal']
            )->keyBy('date');

            // Bollinger Bands
            $bollingerConfig = $config['bollinger'] ?? ['period' => 20, 'std_dev' => 2];
            $bollingerData = $this->calculateBollingerBands(
                $prices,
                $bollingerConfig['period'],
                $bollingerConfig['std_dev']
            )->keyBy('date');

            // Stochastic
            $stochasticData = $this->calculateStochastic($prices)->keyBy('date');

            // ADX
            $adxData = $this->calculateADX($prices)->keyBy('date');

            // ATR
            $atrData = $this->calculateATR($prices)->keyBy('date');

            // Volatility
            $volatilityData = $this->calculateVolatility($prices)->keyBy('date');

            // Get all unique dates that have at least one indicator
            $allDates = collect()
                ->merge($smaData[5]->keys())
                ->merge($emaData[12]->keys())
                ->merge($rsiData->keys())
                ->merge($macdData->keys())
                ->merge($bollingerData->keys())
                ->merge($stochasticData->keys())
                ->merge($adxData->keys())
                ->merge($atrData->keys())
                ->merge($volatilityData->keys())
                ->unique()
                ->sort();

            // Save indicators to database
            foreach ($allDates as $date) {
                $indicator = StockTechnicalIndicator::updateOrCreate(
                    [
                        'stock_id' => $stock->id,
                        'date' => $date,
                    ],
                    [
                        'sma_5' => $smaData[5][$date]['value'] ?? null,
                        'sma_10' => $smaData[10][$date]['value'] ?? null,
                        'sma_20' => $smaData[20][$date]['value'] ?? null,
                        'sma_50' => $smaData[50][$date]['value'] ?? null,
                        'sma_200' => $smaData[200][$date]['value'] ?? null,
                        'ema_12' => $emaData[12][$date]['value'] ?? null,
                        'ema_26' => $emaData[26][$date]['value'] ?? null,
                        'rsi' => $rsiData[$date]['value'] ?? null,
                        'macd' => $macdData[$date]['macd'] ?? null,
                        'macd_signal' => $macdData[$date]['signal'] ?? null,
                        'macd_histogram' => $macdData[$date]['histogram'] ?? null,
                        'bollinger_upper' => $bollingerData[$date]['upper'] ?? null,
                        'bollinger_middle' => $bollingerData[$date]['middle'] ?? null,
                        'bollinger_lower' => $bollingerData[$date]['lower'] ?? null,
                        'stochastic_k' => $stochasticData[$date]['k'] ?? null,
                        'stochastic_d' => $stochasticData[$date]['d'] ?? null,
                        'adx' => $adxData[$date]['value'] ?? null,
                        'atr' => $atrData[$date]['value'] ?? null,
                        'volatility' => $volatilityData[$date]['value'] ?? null,
                    ]
                );
            }

            Log::info('Technical indicators updated', [
                'code' => $stockCode,
                'dates_updated' => $allDates->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update technical indicators', [
                'code' => $stockCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate buy/sell signals from technical indicators.
     *
     * @param Stock $stock
     * @return Collection
     */
    public function generateSignalsFromIndicators(Stock $stock): Collection
    {
        // This method will be used by StockSignalService
        // For now, return empty collection
        return collect();
    }

    /**
     * Helper method to calculate EMA from a collection of values.
     *
     * @param Collection $values Collection with 'date' and 'value' keys
     * @param int $period
     * @return Collection
     */
    protected function calculateEMAFromValues(Collection $values, int $period): Collection
    {
        if ($values->count() < $period) {
            return collect();
        }

        $ema = collect();
        $sortedValues = $values->sortBy('date')->values();
        $multiplier = 2 / ($period + 1);

        // First EMA value is SMA
        $sum = 0;
        for ($i = 0; $i < $period; $i++) {
            $sum += $sortedValues[$i]['value'];
        }
        $previousEMA = $sum / $period;

        $ema->push([
            'date' => $sortedValues[$period - 1]['date'],
            'value' => $previousEMA,
        ]);

        // Calculate subsequent EMA values
        for ($i = $period; $i < $sortedValues->count(); $i++) {
            $currentValue = $sortedValues[$i]['value'];
            $currentEMA = ($currentValue - $previousEMA) * $multiplier + $previousEMA;
            
            $ema->push([
                'date' => $sortedValues[$i]['date'],
                'value' => $currentEMA,
            ]);

            $previousEMA = $currentEMA;
        }

        return $ema;
    }
}

