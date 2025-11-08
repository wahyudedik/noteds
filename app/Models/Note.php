<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Models\NoteConversation;

class Note extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = [
        'user_id',
        'original_creator_id',
        'folder_id',
        'workspace_id',
        'title',
        'content',
        'summary',
        'preview_content',
        'preview_percentage',
        'thumbnails',
        'attachments',
        'file_count',
        'price',
        'discount_price',
        'is_public',
        'status',
        'is_sold',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'is_public' => 'boolean',
            'status' => 'string',
            'is_sold' => 'boolean',
            'attachments' => 'array',
            'thumbnails' => 'array',
            'file_count' => 'integer',
            'preview_percentage' => 'integer',
            'notification_meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the original creator of this note.
     */
    public function originalCreator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'original_creator_id');
    }

    /**
     * Check if note has been sold (can only be sold once).
     */
    public function hasBeenSold(): bool
    {
        return $this->is_sold;
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

    public function histories()
    {
        return $this->hasMany(NoteHistory::class)->latest();
    }

    /**
     * Get the folder that contains this note.
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get the workspace that contains this note.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
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

    /**
     * Get featured notes for this note.
     */
    public function featuredNotes()
    {
        return $this->hasMany(FeaturedNote::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(NoteConversation::class);
    }

    /**
     * Get active featured note for this note.
     */
    public function activeFeaturedNote()
    {
        return $this->hasOne(FeaturedNote::class)->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    /**
     * Get the final price (discount price if available, otherwise regular price).
     */
    public function getFinalPriceAttribute(): float
    {
        if ($this->discount_price !== null && $this->discount_price > 0) {
            return (float) $this->discount_price;
        }
        return (float) $this->price;
    }

    /**
     * Get the discount percentage.
     */
    public function getDiscountPercentAttribute(): ?float
    {
        if ($this->discount_price === null || $this->discount_price <= 0 || $this->price <= 0) {
            return null;
        }
        
        $discount = $this->price - $this->discount_price;
        return round(($discount / $this->price) * 100, 0);
    }

    /**
     * Check if note has discount.
     */
    public function hasDiscount(): bool
    {
        return $this->discount_price !== null 
            && $this->discount_price > 0 
            && $this->discount_price < $this->price;
    }

    /**
     * Get preview content based on preview_percentage.
     * Returns the percentage of content that should be visible based on lines.
     * Example: 100 lines, 50% = 50 lines visible
     */
    public function getPreviewContentByPercentage(): string
    {
        if ($this->preview_percentage <= 0) {
            return ''; // Fully locked
        }

        if ($this->preview_percentage >= 100) {
            return $this->content; // Fully visible
        }

        // Split content by lines (handle both \n and \r\n)
        $content = $this->content;
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $totalLines = count($lines);
        
        // Calculate how many lines to show
        $previewLines = (int) ceil($totalLines * ($this->preview_percentage / 100));
        
        // Take first N lines
        $previewLinesArray = array_slice($lines, 0, $previewLines);
        
        // Join back with newlines
        return implode("\n", $previewLinesArray);
    }

    /**
     * Check if note has thumbnails.
     */
    public function hasThumbnails(): bool
    {
        return !empty($this->thumbnails) && is_array($this->thumbnails) && count($this->thumbnails) > 0;
    }

    /**
     * Get thumbnail count.
     */
    public function getThumbnailCount(): int
    {
        if (!$this->hasThumbnails()) {
            return 0;
        }
        return count($this->thumbnails);
    }

    public function notificationMeta(string $key = null, $default = null)
    {
        $meta = $this->notification_meta ?? [];

        if ($key === null) {
            return $meta;
        }

        return data_get($meta, $key, $default);
    }

    public function setNotificationMetaValue(string $key, $value, bool $save = true): void
    {
        $meta = $this->notification_meta ?? [];
        data_set($meta, $key, $value);
        $this->notification_meta = $meta;

        if ($save) {
            $this->save();
        }
    }

    /**
     * Get purchased notes (users who bought this note).
     */
    public function purchasedBy()
    {
        return $this->hasMany(PurchasedNote::class);
    }

    /**
     * Get reading progress records.
     */
    public function readingProgress()
    {
        return $this->hasMany(ReadingProgress::class);
    }

    /**
     * Get bookmarks.
     */
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Get AI analyses.
     */
    public function aiAnalyses()
    {
        return $this->hasMany(AiAnalysis::class);
    }

    /**
     * Get study materials.
     */
    public function studyMaterials()
    {
        return $this->hasMany(StudyMaterial::class);
    }

    /**
     * Get note downloads.
     */
    public function noteDownloads()
    {
        return $this->hasMany(NoteDownload::class);
    }

    /**
     * Check if a user has purchased this note.
     */
    public function isPurchasedBy($userId): bool
    {
        return $this->purchasedBy()->where('user_id', $userId)->exists();
    }
}
