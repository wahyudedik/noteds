<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SellerPerformanceMetric extends Model
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
        'seller_id',
        'total_orders',
        'completed_orders',
        'cancelled_orders',
        'total_revenue',
        'average_order_value',
        'fulfillment_rate',
        'average_response_time_hours',
        'total_rating',
        'total_reviews',
        'last_calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'total_orders' => 'integer',
            'completed_orders' => 'integer',
            'cancelled_orders' => 'integer',
            'total_revenue' => 'decimal:2',
            'average_order_value' => 'decimal:2',
            'fulfillment_rate' => 'decimal:2',
            'average_response_time_hours' => 'decimal:2',
            'total_rating' => 'decimal:2',
            'total_reviews' => 'integer',
            'last_calculated_at' => 'datetime',
        ];
    }

    /**
     * Get the seller for this performance metric.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Recalculate all metrics for the seller.
     */
    public function recalculate(): void
    {
        // This will be implemented by service layer
    }

    /**
     * Update metrics from an order.
     */
    public function updateFromOrder(Order $order): void
    {
        // This will be implemented by service layer
    }

    /**
     * Update rating metric.
     */
    public function updateRating(float $rating): void
    {
        $this->update([
            'total_rating' => $rating,
            'last_calculated_at' => now(),
        ]);
    }

    /**
     * Scope a query to only include top performers.
     */
    public function scopeTopPerformers(Builder $query, int $limit = 10): Builder
    {
        return $query->orderBy('total_rating', 'desc')
            ->orderBy('total_revenue', 'desc')
            ->limit($limit);
    }

    /**
     * Scope a query to only include low performers.
     */
    public function scopeLowPerformers(Builder $query, int $limit = 10): Builder
    {
        return $query->where('total_orders', '>', 0)
            ->orderBy('total_rating', 'asc')
            ->orderBy('fulfillment_rate', 'asc')
            ->limit($limit);
    }
}
