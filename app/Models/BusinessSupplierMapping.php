<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BusinessSupplierMapping extends Model
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
        'business_type',
        'supplier_category',
        'category_label',
        'priority_order',
        'recommendation_note',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'priority_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the business category.
     */
    public function businessCategory(): BelongsTo
    {
        return $this->belongsTo(BusinessSupplierCategory::class, 'business_type', 'business_type');
    }

    /**
     * Scope to get only active mappings.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by business type.
     */
    public function scopeByBusinessType($query, string $businessType)
    {
        return $query->where('business_type', $businessType);
    }

    /**
     * Scope to order by priority.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('priority_order', 'asc');
    }
}
