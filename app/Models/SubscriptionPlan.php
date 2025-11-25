<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'monthly_price',
        'yearly_price',
        'yearly_discount_percent',
        'features',
        'max_team_members',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'yearly_price' => 'decimal:2',
            'yearly_discount_percent' => 'integer',
            'features' => 'array',
            'max_team_members' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(BuyerSubscription::class, 'plan_id');
    }

    /**
     * Get price for billing cycle.
     */
    public function getPrice(string $billingCycle): float
    {
        return $billingCycle === 'yearly' ? $this->yearly_price : $this->monthly_price;
    }

    /**
     * Calculate yearly savings.
     */
    public function getYearlySavings(): float
    {
        $monthlyTotal = $this->monthly_price * 12;
        return $monthlyTotal - $this->yearly_price;
    }

    /**
     * Get active plans.
     */
    public static function active(): \Illuminate\Database\Eloquent\Builder
    {
        return static::where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Scope for team plans.
     */
    public function scopeTeamPlans($query)
    {
        return $query->whereNotNull('max_team_members');
    }
}
