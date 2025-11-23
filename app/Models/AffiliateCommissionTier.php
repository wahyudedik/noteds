<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateCommissionTier extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'description',
        'min_conversions',
        'min_revenue',
        'tier_1_rate',
        'tier_2_rate',
        'tier_3_rate',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_conversions' => 'integer',
            'min_revenue' => 'decimal:2',
            'tier_1_rate' => 'decimal:2',
            'tier_2_rate' => 'decimal:2',
            'tier_3_rate' => 'decimal:2',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get commission rate for specific tier.
     */
    public function getCommissionRate(int $tier): float
    {
        return match($tier) {
            1 => (float) $this->tier_1_rate,
            2 => (float) $this->tier_2_rate,
            3 => (float) $this->tier_3_rate,
            default => 0.0,
        };
    }

    /**
     * Check if affiliate qualifies for this tier.
     */
    public function qualifies(int $conversions, float $revenue): bool
    {
        return $conversions >= $this->min_conversions && $revenue >= $this->min_revenue;
    }

    /**
     * Scope for active tiers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordered by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('min_revenue');
    }
}
