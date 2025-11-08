<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeaturedNote extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'note_id',
        'user_id',
        'parent_id',
        'location',
        'variant',
        'start_date',
        'end_date',
        'scheduled_date',
        'duration_days',
        'is_custom_duration',
        'price',
        'discount_percent',
        'status',
        'clicks',
        'impressions',
        'admin_notes',
        'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'scheduled_date' => 'date',
            'price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'duration_days' => 'integer',
            'is_custom_duration' => 'boolean',
            'clicks' => 'integer',
            'impressions' => 'integer',
            'reminder_sent_at' => 'datetime',
        ];
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get parent featured note (for bulk purchases).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(FeaturedNote::class, 'parent_id');
    }

    /**
     * Get child featured notes (for bulk purchases).
     */
    public function children()
    {
        return $this->hasMany(FeaturedNote::class, 'parent_id');
    }

    /**
     * Check if this featured note is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && $this->start_date 
            && $this->end_date
            && $this->start_date <= now() 
            && $this->end_date >= now();
    }

    /**
     * Check if this featured note is scheduled for future.
     */
    public function isScheduled(): bool
    {
        return $this->status === 'active' 
            && $this->scheduled_date 
            && $this->scheduled_date > now();
    }

    /**
     * Get final price after discount.
     */
    public function getFinalPriceAttribute(): float
    {
        if ($this->discount_percent > 0) {
            return $this->price * (1 - $this->discount_percent / 100);
        }
        return $this->price;
    }

    /**
     * Get CTR (Click-Through Rate).
     */
    public function getCtrAttribute(): float
    {
        if ($this->impressions > 0) {
            return round(($this->clicks / $this->impressions) * 100, 2);
        }
        return 0;
    }

    /**
     * Scope to get only active featured notes.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    /**
     * Scope to get featured notes by location.
     */
    public function scopeByLocation($query, string $location)
    {
        return $query->where('location', $location);
    }

    /**
     * Increment impressions counter.
     */
    public function incrementImpressions(): void
    {
        $this->increment('impressions');
    }

    /**
     * Increment clicks counter.
     */
    public function incrementClicks(): void
    {
        $this->increment('clicks');
    }
}
