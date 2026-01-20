<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Stock extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'sector',
        'sub_sector',
        'listing_date',
        'is_active',
        'market_cap',
        'category',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'market_cap' => 'decimal:2',
            'listing_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the prices for this stock.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(StockPrice::class);
    }

    /**
     * Get the technical indicators for this stock.
     */
    public function technicalIndicators(): HasMany
    {
        return $this->hasMany(StockTechnicalIndicator::class);
    }

    /**
     * Get the predictions for this stock.
     */
    public function predictions(): HasMany
    {
        return $this->hasMany(StockPrediction::class);
    }

    /**
     * Get the ML models for this stock.
     */
    public function mlModels(): HasMany
    {
        return $this->hasMany(MlModel::class);
    }

    /**
     * Get the signals for this stock.
     */
    public function signals(): HasMany
    {
        return $this->hasMany(StockSignal::class);
    }

    /**
     * Get the watchlists for this stock.
     */
    public function watchlists(): HasMany
    {
        return $this->hasMany(StockWatchlist::class);
    }

    /**
     * Get the latest price for this stock.
     */
    public function getLatestPrice(): ?StockPrice
    {
        return $this->prices()
            ->where('is_intraday', false)
            ->latest('date')
            ->first();
    }

    /**
     * Get the price at a specific date.
     */
    public function getPriceAt(Carbon $date): ?StockPrice
    {
        return $this->prices()
            ->where('date', $date->format('Y-m-d'))
            ->where('is_intraday', false)
            ->first();
    }

    /**
     * Check if stock is in a specific category.
     */
    public function isInCategory(string $category): bool
    {
        return $this->category === $category;
    }

    /**
     * Get market capitalization.
     */
    public function getMarketCap(): ?float
    {
        return $this->market_cap ? (float) $this->market_cap : null;
    }

    /**
     * Scope a query to only include active stocks.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to filter by sector.
     */
    public function scopeBySector($query, string $sector)
    {
        return $query->where('sector', $sector);
    }
}

