<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
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
        'user_id',
        'post_id',
        'parent_product_id',
        'variant_type',
        'variant_value',
        'name',
        'slug',
        'description',
        'price',
        'category',
        'image',
        'file_download',
        'license_key',
        'is_active',
        'stock',
        'sales_count',
        'views_count',
        'is_bundle',
        'bundle_price',
        'bundle_discount_percentage',
        'is_subscription',
        'subscription_interval',
        'subscription_duration',
        'trial_days',
        'is_waitlist_enabled',
        'waitlist_notify_at_stock',
    ];

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'stock' => 'integer',
            'sales_count' => 'integer',
            'views_count' => 'integer',
            'is_bundle' => 'boolean',
            'bundle_price' => 'decimal:2',
            'bundle_discount_percentage' => 'decimal:2',
            'is_subscription' => 'boolean',
            'subscription_duration' => 'integer',
            'trial_days' => 'integer',
            'is_waitlist_enabled' => 'boolean',
            'waitlist_notify_at_stock' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
                
                // Ensure uniqueness
                $originalSlug = $product->slug;
                $count = 1;
                while (static::where('slug', $product->slug)->exists()) {
                    $product->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    /**
     * Get the seller (user) that owns the product.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the post associated with this product (if cross-posted).
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the orders for the product.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the reviews for the product.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * Calculate average rating for the product.
     */
    public function averageRating(): float
    {
        return (float) $this->reviews()->avg('rating') ?? 0;
    }

    /**
     * Get the parent product (for variants).
     */
    public function parentProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'parent_product_id');
    }

    /**
     * Get the variants of this product.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(Product::class, 'parent_product_id');
    }

    /**
     * Get bundle items.
     */
    public function bundleItems(): HasMany
    {
        return $this->hasMany(ProductBundleItem::class, 'bundle_id');
    }

    /**
     * Get bundles that include this product.
     */
    public function bundles(): HasMany
    {
        return $this->hasMany(ProductBundleItem::class, 'product_id')
            ->with('bundle');
    }

    /**
     * Get coupons for this product.
     */
    public function coupons(): HasMany
    {
        return $this->hasMany(ProductCoupon::class);
    }

    /**
     * Get waitlist entries.
     */
    public function waitlists(): HasMany
    {
        return $this->hasMany(ProductWaitlist::class);
    }

    /**
     * Get subscriptions for this product.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(ProductSubscription::class);
    }

    /**
     * Check if product is a variant.
     */
    public function isVariant(): bool
    {
        return $this->parent_product_id !== null;
    }

    /**
     * Check if product is a bundle.
     */
    public function isBundle(): bool
    {
        return $this->is_bundle ?? false;
    }

    /**
     * Check if product is a subscription.
     */
    public function isSubscription(): bool
    {
        return $this->is_subscription ?? false;
    }

    /**
     * Check if product has variants.
     */
    public function hasVariants(): bool
    {
        return $this->variants()->exists();
    }

    /**
     * Get bundle price (fixed or calculated).
     */
    public function getBundlePrice(): float
    {
        if ($this->bundle_price) {
            return (float) $this->bundle_price;
        }

        return $this->calculateBundleTotal();
    }

    /**
     * Calculate bundle total from items.
     */
    public function calculateBundleTotal(): float
    {
        $total = 0;
        foreach ($this->bundleItems as $item) {
            $total += $item->product->price * $item->quantity;
        }

        if ($this->bundle_discount_percentage) {
            $discount = ($total * $this->bundle_discount_percentage) / 100;
            $total -= $discount;
        }

        return $total;
    }

    /**
     * Scope to filter variants.
     */
    public function scopeVariants($query)
    {
        return $query->whereNotNull('parent_product_id');
    }

    /**
     * Scope to filter bundles.
     */
    public function scopeBundles($query)
    {
        return $query->where('is_bundle', true);
    }

    /**
     * Scope to filter subscriptions.
     */
    public function scopeSubscriptions($query)
    {
        return $query->where('is_subscription', true);
    }

    /**
     * Scope to filter products with waitlist enabled.
     */
    public function scopeWithWaitlist($query)
    {
        return $query->where('is_waitlist_enabled', true);
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get the image URL attribute.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        return asset('storage/' . $this->image);
    }
}
