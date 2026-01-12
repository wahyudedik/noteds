<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MlModel extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'model_type',
        'stock_id',
        'model_version',
        'status',
        'training_started_at',
        'training_completed_at',
        'metrics',
        'hyperparameters',
        'file_path',
        'prediction_horizon',
        'is_best_model',
    ];

    protected function casts(): array
    {
        return [
            'metrics' => 'array',
            'hyperparameters' => 'array',
            'training_started_at' => 'datetime',
            'training_completed_at' => 'datetime',
            'prediction_horizon' => 'integer',
            'is_best_model' => 'boolean',
        ];
    }

    /**
     * Get the stock that owns this model (nullable for general models).
     */
    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Get the predictions for this model.
     */
    public function predictions(): HasMany
    {
        return $this->hasMany(StockPrediction::class);
    }

    /**
     * Get the signals for this model.
     */
    public function signals(): HasMany
    {
        return $this->hasMany(StockSignal::class);
    }

    /**
     * Get the best model for a stock and prediction horizon.
     */
    public static function getBestModelForStock(string $stockId, int $horizon): ?self
    {
        return static::where('stock_id', $stockId)
            ->where('prediction_horizon', $horizon)
            ->where('status', 'active')
            ->where('is_best_model', true)
            ->first();
    }

    /**
     * Check if model is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get model accuracy from metrics.
     */
    public function getAccuracy(): ?float
    {
        if (!$this->metrics || !isset($this->metrics['accuracy'])) {
            return null;
        }

        return (float) $this->metrics['accuracy'];
    }

    /**
     * Scope a query to only include active models.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to filter by model type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('model_type', $type);
    }

    /**
     * Scope a query to only include best models.
     */
    public function scopeBest($query)
    {
        return $query->where('is_best_model', true);
    }

    /**
     * Scope a query to filter by prediction horizon.
     */
    public function scopeByPredictionHorizon($query, int $horizon)
    {
        return $query->where('prediction_horizon', $horizon);
    }
}

