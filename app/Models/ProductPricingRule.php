<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProductPricingRule extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (!isset($this->attributes['id'])) {
            $this->attributes['id'] = (string) Str::uuid();
        }
    }

    protected $fillable = [
        'product_id',
        'rule_type',
        'name',
        'is_active',
        'priority',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'days_of_week',
        'stock_threshold',
        'stock_condition',
        'sales_period_days',
        'sales_threshold',
        'demand_condition',
        'adjustment_type',
        'adjustment_value',
        'base_price_override',
        'max_applications',
        'application_count',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'days_of_week' => 'array',
            'priority' => 'integer',
            'stock_threshold' => 'integer',
            'sales_period_days' => 'integer',
            'sales_threshold' => 'integer',
            'adjustment_value' => 'decimal:2',
            'base_price_override' => 'decimal:2',
            'max_applications' => 'integer',
            'application_count' => 'integer',
            'metadata' => 'array',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }

    /**
     * Get the product for this pricing rule.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get all applications of this rule.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(ProductPricingRuleApplication::class, 'rule_id');
    }

    /**
     * Check if rule is applicable at current time/conditions.
     */
    public function isApplicable(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (!$this->canStillApply()) {
            return false;
        }

        if ($this->rule_type === 'time_based') {
            return $this->shouldTriggerTimeBased();
        } elseif ($this->rule_type === 'stock_based') {
            return $this->shouldTriggerStockBased();
        } elseif ($this->rule_type === 'demand_based') {
            return $this->shouldTriggerDemandBased();
        }

        return false;
    }

    /**
     * Calculate price adjustment based on this rule.
     */
    public function calculatePrice(float $basePrice): float
    {
        $price = $this->base_price_override ?? $basePrice;

        if ($this->adjustment_type === 'fixed') {
            $adjustedPrice = $price + (float) $this->adjustment_value;
        } else { // percentage
            $adjustedPrice = $price + ($price * (float) $this->adjustment_value / 100);
        }

        return max(0, $adjustedPrice);
    }

    /**
     * Apply rule to product.
     */
    public function applyToProduct(): ?float
    {
        if (!$this->isApplicable()) {
            return null;
        }

        $product = $this->product;
        $basePrice = $this->base_price_override ?? ($product->base_price ?? $product->price);
        $adjustedPrice = $this->calculatePrice($basePrice);

        // Increment application count
        $this->increment('application_count');

        return $adjustedPrice;
    }

    /**
     * Check if rule should trigger (for time/stock/demand based).
     */
    public function shouldTrigger(): bool
    {
        return $this->isApplicable();
    }

    /**
     * Check if rule can still apply (max_applications limit).
     */
    public function canStillApply(): bool
    {
        if ($this->max_applications === null) {
            return true;
        }

        return $this->application_count < $this->max_applications;
    }

    /**
     * Check if time-based rule should trigger.
     */
    protected function shouldTriggerTimeBased(): bool
    {
        $now = Carbon::now();

        // Check date range
        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        // Check time of day
        if ($this->start_time && $this->end_time) {
            $currentTime = $now->format('H:i:s');
            if ($currentTime < $this->start_time || $currentTime > $this->end_time) {
                return false;
            }
        }

        // Check days of week
        if ($this->days_of_week && is_array($this->days_of_week) && count($this->days_of_week) > 0) {
            $currentDay = $now->dayOfWeek; // 0 = Sunday, 6 = Saturday
            if (!in_array($currentDay, $this->days_of_week)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if stock-based rule should trigger.
     */
    protected function shouldTriggerStockBased(): bool
    {
        $product = $this->product;
        $currentStock = $product->stock ?? 0;
        $threshold = $this->stock_threshold;

        if ($threshold === null) {
            return false;
        }

        return match ($this->stock_condition) {
            'below' => $currentStock < $threshold,
            'above' => $currentStock > $threshold,
            'equals' => $currentStock === $threshold,
            default => false,
        };
    }

    /**
     * Check if demand-based rule should trigger.
     */
    protected function shouldTriggerDemandBased(): bool
    {
        $product = $this->product;
        $periodDays = $this->sales_period_days ?? 7;
        $threshold = $this->sales_threshold ?? 0;

        $salesCount = Order::where('product_id', $product->id)
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subDays($periodDays))
            ->count();

        return match ($this->demand_condition) {
            'high' => $salesCount >= $threshold,
            'low' => $salesCount < $threshold,
            default => false,
        };
    }

    /**
     * Scope a query to only include active rules.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include rules for a specific product.
     */
    public function scopeForProduct(Builder $query, string $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to only include rules of a specific type.
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('rule_type', $type);
    }

    /**
     * Scope a query to order by priority (highest first).
     */
    public function scopeByPriority(Builder $query): Builder
    {
        return $query->orderBy('priority', 'desc');
    }
}
