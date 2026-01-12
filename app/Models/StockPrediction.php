<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class StockPrediction extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'stock_id',
        'ml_model_id',
        'prediction_date',
        'target_date',
        'predicted_price',
        'confidence_score',
        'lower_bound',
        'upper_bound',
        'prediction_horizon',
        'actual_price',
        'prediction_error',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'prediction_date' => 'date',
            'target_date' => 'date',
            'predicted_price' => 'decimal:2',
            'confidence_score' => 'decimal:4',
            'lower_bound' => 'decimal:2',
            'upper_bound' => 'decimal:2',
            'prediction_horizon' => 'integer',
            'actual_price' => 'decimal:2',
            'prediction_error' => 'decimal:4',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the stock that owns this prediction.
     */
    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Get the ML model that generated this prediction.
     */
    public function mlModel(): BelongsTo
    {
        return $this->belongsTo(MlModel::class);
    }

    /**
     * Calculate prediction error if actual price is available.
     */
    public function calculateError(): ?float
    {
        if ($this->actual_price === null || $this->predicted_price === null) {
            return null;
        }

        $error = abs($this->actual_price - $this->predicted_price);
        $this->prediction_error = $error;
        $this->save();

        return $error;
    }

    /**
     * Get prediction accuracy (percentage).
     */
    public function getPredictionAccuracy(): ?float
    {
        if ($this->actual_price === null || $this->predicted_price === null || $this->actual_price == 0) {
            return null;
        }

        $error = abs($this->actual_price - $this->predicted_price);
        $accuracy = (1 - ($error / $this->actual_price)) * 100;

        return max(0, min(100, $accuracy));
    }

    /**
     * Check if prediction is accurate within threshold.
     */
    public function isAccurate(float $threshold = 0.05): bool
    {
        if ($this->actual_price === null || $this->predicted_price === null || $this->actual_price == 0) {
            return false;
        }

        $errorPercentage = abs($this->actual_price - $this->predicted_price) / $this->actual_price;
        return $errorPercentage <= $threshold;
    }

    /**
     * Scope a query to only include latest predictions.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('prediction_date', 'desc')
            ->orderBy('target_date', 'desc');
    }

    /**
     * Scope a query to filter by prediction horizon.
     */
    public function scopeByHorizon($query, int $horizon)
    {
        return $query->where('prediction_horizon', $horizon);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeByDateRange($query, Carbon $from, Carbon $to)
    {
        return $query->whereBetween('target_date', [$from, $to]);
    }

    /**
     * Scope a query to only include predictions with actual prices.
     */
    public function scopeWithActuals($query)
    {
        return $query->whereNotNull('actual_price');
    }
}

