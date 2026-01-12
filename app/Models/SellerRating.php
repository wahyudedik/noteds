<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SellerRating extends Model
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
        'buyer_id',
        'order_id',
        'rating',
        'review_rating',
        'fulfillment_rating',
        'response_rating',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
            'review_rating' => 'decimal:2',
            'fulfillment_rating' => 'decimal:2',
            'response_rating' => 'decimal:2',
        ];
    }

    /**
     * Get the seller being rated.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the buyer who gave the rating.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the order associated with this rating.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Calculate weighted rating from components.
     */
    public function calculateWeightedRating(): float
    {
        $weights = config('seller.rating.weights', [
            'review' => 0.40,
            'fulfillment' => 0.35,
            'response_time' => 0.25,
        ]);

        $reviewRating = (float) ($this->review_rating ?? 0);
        $fulfillmentRating = (float) ($this->fulfillment_rating ?? 0);
        $responseRating = (float) ($this->response_rating ?? 0);

        $weightedRating = ($reviewRating * $weights['review'])
            + ($fulfillmentRating * $weights['fulfillment'])
            + ($responseRating * $weights['response_time']);

        return round($weightedRating, 2);
    }

    /**
     * Update seller performance metrics after rating is created/updated.
     */
    public function updateSellerPerformance(): void
    {
        // This will be called by service layer
        // to recalculate seller metrics
    }

    /**
     * Scope a query to only include ratings for a specific seller.
     */
    public function scopeForSeller(Builder $query, string $sellerId): Builder
    {
        return $query->where('seller_id', $sellerId);
    }

    /**
     * Scope a query to only include ratings by a specific buyer.
     */
    public function scopeByBuyer(Builder $query, string $buyerId): Builder
    {
        return $query->where('buyer_id', $buyerId);
    }
}
