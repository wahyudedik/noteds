<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Supplier extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Create a new Eloquent model instance.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (!isset($this->attributes['id'])) {
            $this->attributes['id'] = (string) Str::uuid();
        }
    }

    protected $fillable = [
        'user_id',
        'supplier_name',
        'supplier_category',
        'description',
        'location',
        'contact_info',
        'specialties',
        'min_order_amount',
        'delivery_scope',
        'rating',
        'review_count',
        'order_count',
        'view_count',
        'is_verified',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'contact_info' => 'array',
            'specialties' => 'array',
            'min_order_amount' => 'decimal:2',
            'rating' => 'decimal:2',
            'review_count' => 'integer',
            'order_count' => 'integer',
            'view_count' => 'integer',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the seller (user) that owns the supplier.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the reviews for this supplier.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(SupplierReview::class);
    }

    /**
     * Get products from this supplier's seller (if any match the category).
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'user_id', 'user_id')
            ->where('category', $this->supplier_category);
    }

    /**
     * Update supplier rating from reviews.
     */
    public function updateRating(): void
    {
        $avgRating = $this->reviews()->avg('rating');
        $reviewCount = $this->reviews()->count();

        $this->update([
            'rating' => $avgRating ? round($avgRating, 2) : 0,
            'review_count' => $reviewCount,
        ]);
    }

    /**
     * Scope to get only active suppliers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only verified suppliers.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('supplier_category', $category);
    }

    /**
     * Scope to filter by location.
     */
    public function scopeByLocation($query, string $location)
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    /**
     * Scope to get top rated suppliers.
     */
    public function scopeTopRated($query, int $limit = 10)
    {
        return $query->orderBy('rating', 'desc')
            ->orderBy('review_count', 'desc')
            ->limit($limit);
    }
}
