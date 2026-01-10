<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductReviewVote extends Model
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
        'user_id',
        'product_review_id',
        'vote_type',
    ];

    protected function casts(): array
    {
        return [
            'vote_type' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function productReview(): BelongsTo
    {
        return $this->belongsTo(ProductReview::class);
    }

    /**
     * Check if vote is helpful.
     */
    public function isHelpful(): bool
    {
        return $this->vote_type === 'helpful';
    }

    /**
     * Check if vote is not helpful.
     */
    public function isNotHelpful(): bool
    {
        return $this->vote_type === 'not_helpful';
    }
}
