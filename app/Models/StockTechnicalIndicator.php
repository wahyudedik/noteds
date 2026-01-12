<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class StockTechnicalIndicator extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'stock_id',
        'date',
        'sma_5',
        'sma_10',
        'sma_20',
        'sma_50',
        'sma_200',
        'ema_12',
        'ema_26',
        'rsi',
        'macd',
        'macd_signal',
        'macd_histogram',
        'bollinger_upper',
        'bollinger_middle',
        'bollinger_lower',
        'stochastic_k',
        'stochastic_d',
        'adx',
        'atr',
        'volatility',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'sma_5' => 'decimal:2',
            'sma_10' => 'decimal:2',
            'sma_20' => 'decimal:2',
            'sma_50' => 'decimal:2',
            'sma_200' => 'decimal:2',
            'ema_12' => 'decimal:2',
            'ema_26' => 'decimal:2',
            'rsi' => 'decimal:2',
            'macd' => 'decimal:2',
            'macd_signal' => 'decimal:2',
            'macd_histogram' => 'decimal:2',
            'bollinger_upper' => 'decimal:2',
            'bollinger_middle' => 'decimal:2',
            'bollinger_lower' => 'decimal:2',
            'stochastic_k' => 'decimal:2',
            'stochastic_d' => 'decimal:2',
            'adx' => 'decimal:2',
            'atr' => 'decimal:2',
            'volatility' => 'decimal:4',
        ];
    }

    /**
     * Get the stock that owns this technical indicator.
     */
    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Get signal strength based on indicators.
     */
    public function getSignalStrength(): float
    {
        $strength = 0.0;
        $factors = 0;

        // RSI signals
        if ($this->rsi !== null) {
            if ($this->rsi < 30) {
                $strength += 0.3; // Oversold, potential buy
            } elseif ($this->rsi > 70) {
                $strength -= 0.3; // Overbought, potential sell
            }
            $factors++;
        }

        // MACD signals
        if ($this->macd !== null && $this->macd_signal !== null) {
            if ($this->macd > $this->macd_signal && $this->macd_histogram > 0) {
                $strength += 0.2; // Bullish
            } elseif ($this->macd < $this->macd_signal && $this->macd_histogram < 0) {
                $strength -= 0.2; // Bearish
            }
            $factors++;
        }

        // Stochastic signals
        if ($this->stochastic_k !== null && $this->stochastic_d !== null) {
            if ($this->stochastic_k < 20 && $this->stochastic_k < $this->stochastic_d) {
                $strength += 0.2; // Oversold
            } elseif ($this->stochastic_k > 80 && $this->stochastic_k > $this->stochastic_d) {
                $strength -= 0.2; // Overbought
            }
            $factors++;
        }

        return $factors > 0 ? max(-1.0, min(1.0, $strength / $factors)) : 0.0;
    }

    /**
     * Get trend direction.
     */
    public function getTrend(): string
    {
        if ($this->sma_20 === null || $this->sma_50 === null) {
            return 'neutral';
        }

        if ($this->sma_20 > $this->sma_50) {
            return 'bullish';
        } elseif ($this->sma_20 < $this->sma_50) {
            return 'bearish';
        }

        return 'neutral';
    }

    /**
     * Check if stock is oversold.
     */
    public function isOversold(): bool
    {
        return $this->rsi !== null && $this->rsi < 30;
    }

    /**
     * Check if stock is overbought.
     */
    public function isOverbought(): bool
    {
        return $this->rsi !== null && $this->rsi > 70;
    }

    /**
     * Scope a query to only include latest indicators.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('date', 'desc');
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeByDateRange($query, Carbon $from, Carbon $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }
}

