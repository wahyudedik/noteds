<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductReviewReply extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        if (!isset($this->attributes['id'])) {
            $this->attributes['id'] = (string) Str::uuid();
        }
    }

    protected $fillable = [
        'product_review_id',
        'seller_id',
        'content',
    ];

    protected static function boot()
    {
        parent::boot();

        // When a reply is created, lock the review
        static::created(function ($reply) {
            if ($reply->productReview) {
                $reply->productReview->lock();
            }
        });

        // When a reply is deleted, unlock the review
        static::deleted(function ($reply) {
            // Use find() to get the review from database since it might be soft deleted
            $review = ProductReview::find($reply->product_review_id);
            if ($review) {
                $review->unlock();
            }
        });
    }

    public function productReview(): BelongsTo
    {
        return $this->belongsTo(ProductReview::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
