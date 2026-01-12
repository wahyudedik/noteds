<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class StockPrice extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'stock_id',
        'date',
        'open',
        'high',
        'low',
        'close',
        'volume',
        'value',
        'frequency',
        'is_intraday',
        'timestamp',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'open' => 'decimal:2',
            'high' => 'decimal:2',
            'low' => 'decimal:2',
            'close' => 'decimal:2',
            'volume' => 'integer',
            'value' => 'decimal:2',
            'frequency' => 'integer',
            'is_intraday' => 'boolean',
            'timestamp' => 'datetime',
        ];
    }

    /**
     * Get the stock that owns this price.
     */
    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Calculate returns (percentage change from open to close).
     */
    public function calculateReturns(): ?float
    {
        if (!$this->open || $this->open == 0) {
            return null;
        }

        return (($this->close - $this->open) / $this->open) * 100;
    }

    /**
     * Get price change (absolute difference).
     */
    public function getPriceChange(): float
    {
        return $this->close - $this->open;
    }

    /**
     * Check if price went up.
     */
    public function isPriceUp(): bool
    {
        return $this->close > $this->open;
    }

    /**
     * Scope a query to only include latest prices.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('date', 'desc')
            ->orderBy('timestamp', 'desc');
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeByDateRange($query, Carbon $from, Carbon $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    /**
     * Scope a query to only include intraday prices.
     */
    public function scopeIntraday($query)
    {
        return $query->where('is_intraday', true);
    }
}

