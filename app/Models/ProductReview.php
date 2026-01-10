<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class ProductReview extends Model
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
        'product_id',
        'order_id',
        'rating',
        'comment',
        'status',
        'is_verified_purchase',
        'helpful_count',
        'is_locked',
        'locked_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_verified_purchase' => 'boolean',
        'helpful_count' => 'integer',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductReviewMedia::class)->orderBy('order');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ProductReviewVote::class);
    }

    public function helpfulVotes(): HasMany
    {
        return $this->hasMany(ProductReviewVote::class)->where('vote_type', 'helpful');
    }

    public function reply(): HasOne
    {
        return $this->hasOne(ProductReviewReply::class);
    }

    public function moderationLogs(): HasMany
    {
        return $this->hasMany(ModerationLog::class);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-verify purchase when order is completed
        static::saving(function ($review) {
            if ($review->order_id) {
                // Load order if not already loaded
                if (!$review->relationLoaded('order')) {
                    $order = Order::find($review->order_id);
                } else {
                    $order = $review->order;
                }
                
                if ($order && $order->status === 'completed' && $order->payment_status === 'paid') {
                    $review->is_verified_purchase = true;
                }
            }
        });
    }

    /**
     * Lock the review (after seller reply).
     */
    public function lock(): void
    {
        $this->update([
            'is_locked' => true,
            'locked_at' => now(),
        ]);
    }

    /**
     * Unlock the review (when seller reply is deleted).
     */
    public function unlock(): void
    {
        $this->update([
            'is_locked' => false,
            'locked_at' => null,
        ]);
    }

    /**
     * Check if review is locked.
     */
    public function isLocked(): bool
    {
        return $this->is_locked ?? false;
    }

    /**
     * Check if review can be edited.
     */
    public function canBeEdited(): bool
    {
        return !$this->isLocked();
    }

    /**
     * Scope to filter verified purchases.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified_purchase', true);
    }

    /**
     * Scope to filter active reviews.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter moderated reviews.
     */
    public function scopeModerated($query)
    {
        return $query->where('status', 'moderated');
    }

    /**
     * Scope to eager load media.
     */
    public function scopeWithMedia($query)
    {
        return $query->with('media');
    }

    /**
     * Scope to eager load votes.
     */
    public function scopeWithVotes($query)
    {
        return $query->with('votes');
    }

    /**
     * Scope to eager load reply.
     */
    public function scopeWithReply($query)
    {
        return $query->with('reply.seller');
    }
}
