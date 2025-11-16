<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NoteBundle extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'discount_percentage',
        'image',
        'is_active',
        'purchase_count',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'is_active' => 'boolean',
            'purchase_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(NoteBundleItem::class, 'bundle_id')->orderBy('order');
    }

    public function notes()
    {
        return $this->belongsToMany(Note::class, 'note_bundle_items', 'bundle_id', 'note_id')
            ->withPivot('order')
            ->orderBy('note_bundle_items.order');
    }

    public function getTotalOriginalPriceAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->note->price ?? 0;
        });
    }

    public function getDiscountAmountAttribute(): float
    {
        $originalPrice = $this->total_original_price;
        return ($originalPrice * $this->discount_percentage) / 100;
    }

    public function getFinalPriceAttribute(): float
    {
        return $this->price;
    }
}
