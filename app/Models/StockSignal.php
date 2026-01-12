<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class StockSignal extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'stock_id',
        'signal_type',
        'signal_strength',
        'signal_date',
        'source',
        'ml_model_id',
        'technical_indicators',
        'reason',
        'price_target',
        'stop_loss',
        'take_profit',
        'risk_level',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'signal_strength' => 'decimal:2',
            'signal_date' => 'date',
            'technical_indicators' => 'array',
            'price_target' => 'decimal:2',
            'stop_loss' => 'decimal:2',
            'take_profit' => 'decimal:2',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Get the stock that owns this signal.
     */
    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Get the ML model that generated this signal (nullable).
     */
    public function mlModel(): BelongsTo
    {
        return $this->belongsTo(MlModel::class);
    }

    /**
     * Check if signal is expired.
     */
    public function isExpired(): bool
    {
        if ($this->expires_at === null) {
            return false;
        }

        return Carbon::now()->greaterThan($this->expires_at);
    }

    /**
     * Check if signal is valid (not expired).
     */
    public function isValid(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Get risk score (0-1) based on risk level.
     */
    public function getRiskScore(): float
    {
        return match ($this->risk_level) {
            'low' => 0.25,
            'medium' => 0.5,
            'high' => 0.75,
            'very_high' => 1.0,
            default => 0.5,
        };
    }

    /**
     * Scope a query to only include active (non-expired) signals.
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', Carbon::now());
        });
    }

    /**
     * Scope a query to filter by signal type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('signal_type', $type);
    }

    /**
     * Scope a query to filter by risk level.
     */
    public function scopeByRiskLevel($query, string $level)
    {
        return $query->where('risk_level', $level);
    }

    /**
     * Scope a query to only include latest signals.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('signal_date', 'desc')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Scope a query to only include expired signals.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', Carbon::now());
    }
}

