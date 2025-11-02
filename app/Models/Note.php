<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Note extends Model
{
    use HasUuids;
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'summary',
        'preview_content',
        'attachments',
        'file_count',
        'price',
        'is_public',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_public' => 'boolean',
            'status' => 'string',
            'attachments' => 'array',
            'file_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function reviews()
    {
        return $this->hasMany(NoteReview::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function activities()
    {
        return $this->hasMany(NoteActivity::class)->latest();
    }

    /**
     * Get purchase count (number of successful transactions)
     */
    public function getPurchaseCountAttribute(): int
    {
        if ($this->relationLoaded('transactions')) {
            return $this->transactions->where('status', 'success')->count();
        }
        return $this->transactions()->where('status', 'success')->count();
    }

    /**
     * Check if attachments exist
     */
    public function hasAttachments(): bool
    {
        return !empty($this->attachments) && is_array($this->attachments) && count($this->attachments) > 0;
    }

    /**
     * Get preview content or auto-generate from content
     */
    public function getPreviewContentAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }
        
        // Auto-generate preview from content (first 300 chars, strip HTML)
        $content = strip_tags($this->attributes['content'] ?? '');
        return Str::limit($content, 300);
    }

    public function scopePublicOnly($query)
    {
        return $query->where('is_public', true)->where('status', 'active');
    }

    /**
     * Get the average rating for this note.
     */
    public function getAverageRatingAttribute(): float
    {
        // Use the loaded reviews collection if available to avoid extra query
        if ($this->relationLoaded('reviews')) {
            if ($this->reviews->isEmpty()) {
                return 0;
            }
            return round($this->reviews->avg('rating'), 1);
        }
        
        // Fallback to query if not eager loaded
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }

    /**
     * Get the total number of reviews.
     */
    public function getTotalReviewsAttribute(): int
    {
        // Use the loaded reviews collection if available to avoid extra query
        if ($this->relationLoaded('reviews')) {
            return $this->reviews->count();
        }
        
        // Fallback to query if not eager loaded
        return $this->reviews()->count();
    }
}
