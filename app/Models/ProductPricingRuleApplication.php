<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductPricingRuleApplication extends Model
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
        'rule_id',
        'product_id',
        'order_id',
        'original_price',
        'adjusted_price',
        'adjustment_amount',
        'applied_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'original_price' => 'decimal:2',
            'adjusted_price' => 'decimal:2',
            'adjustment_amount' => 'decimal:2',
            'metadata' => 'array',
            'applied_at' => 'datetime',
        ];
    }

    /**
     * Get the pricing rule that was applied.
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(ProductPricingRule::class, 'rule_id');
    }

    /**
     * Get the product for this application.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the order associated with this application (if any).
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
