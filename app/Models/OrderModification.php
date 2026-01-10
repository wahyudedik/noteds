<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OrderModification extends Model
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
        'order_id',
        'modification_type',
        'old_data',
        'new_data',
        'modified_by',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'old_data' => 'array',
            'new_data' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    /**
     * Get modification type label.
     */
    public function getModificationTypeLabel(): string
    {
        return match ($this->modification_type) {
            'quantity' => 'Quantity Changed',
            'product' => 'Product Changed',
            'coupon' => 'Coupon Changed',
            'all' => 'Multiple Changes',
            default => ucfirst($this->modification_type),
        };
    }
}
