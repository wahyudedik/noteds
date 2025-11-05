<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeaturedNote extends Model
{
    use HasUuids;

    protected $fillable = [
        'note_id',
        'user_id',
        'location',
        'start_date',
        'end_date',
        'duration_days',
        'price',
        'status',
        'clicks',
        'impressions',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'price' => 'decimal:2',
            'duration_days' => 'integer',
            'clicks' => 'integer',
            'impressions' => 'integer',
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
