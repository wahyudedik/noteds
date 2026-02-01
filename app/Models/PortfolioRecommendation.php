<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioRecommendation extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'risk_profile',
        'investment_amount',
        'investment_horizon',
        'allocation',
        'expected_return',
        'expected_risk',
        'sharpe_ratio',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'investment_amount' => 'decimal:2',
            'investment_horizon' => 'integer',
            'allocation' => 'array',
            'expected_return' => 'decimal:4',
            'expected_risk' => 'decimal:4',
            'sharpe_ratio' => 'decimal:4',
            'generated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns this portfolio recommendation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate expected returns based on allocation.
     */
    public function calculateReturns(): float
    {
        return $this->expected_return ?? 0.0;
    }

    /**
     * Get risk metrics.
     */
    public function getRiskMetrics(): array
    {
        return [
            'expected_risk' => $this->expected_risk,
            'sharpe_ratio' => $this->sharpe_ratio,
            'risk_profile' => $this->risk_profile,
        ];
    }

    /**
     * Get allocation breakdown with stock details.
     */
    public function getAllocationBreakdown(): array
    {
        $allocation = $this->allocation ?? [];
        $breakdown = [];

        foreach ($allocation as $stockId => $percentage) {
            $stock = Stock::find($stockId);
            if ($stock) {
                $breakdown[] = [
                    'stock' => $stock,
                    'stock_id' => $stockId,
                    'stock_code' => $stock->code,
                    'stock_name' => $stock->name,
                    'percentage' => $percentage,
                    'amount' => ($this->investment_amount * $percentage) / 100,
                ];
            }
        }

        return $breakdown;
    }

    /**
     * Scope a query to only include latest recommendations.
     */
    public function scopeLatest($query, $column = 'generated_at')
    {
        return $query->orderBy($column, 'desc');
    }

    /**
     * Scope a query to filter by risk profile.
     */
    public function scopeByRiskProfile($query, string $profile)
    {
        return $query->where('risk_profile', $profile);
    }

    /**
     * Scope a query to filter by user.
     */
    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }
}
