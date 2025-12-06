<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\NoteConversation;
use App\Models\NoteReport;
use App\Models\Transaction;
use App\Models\Refund;
use App\Models\GiftNote;
use App\Models\NoteComment;
use App\Models\NoteReaction;
use App\Models\NoteQuestion;
use App\Models\NoteViewHistory;
use App\Models\NoteViewRevenue;
use App\Models\NoteBundle;
use App\Models\Category;
use App\Models\NoteSeries;
use App\Models\NoteShareReferral;
use App\Models\VirusScan;
use App\Models\WatermarkSetting;
use App\Models\DrmSetting;
use App\Models\NoteCollaborator;
use App\Models\NoteVersion;
use App\Models\NoteCollaborationComment;
use App\Models\NoteCollaborationSession;

class Note extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = [
        'user_id',
        'original_creator_id',
        'folder_id',
        'workspace_id',
        'series_id',
        'series_order',
        'title',
        'content',
        'content_hash',
        'summary',
        'language',
        'preview_content',
        'preview_percentage',
        'thumbnails',
        'attachments',
        'video_preview',
        'video_preview_thumbnail',
        'video_preview_duration',
        'file_count',
        'price',
        'discount_price',
        'monetization_approved',
        'monetization_auto_approved',
        'monetization_approved_by',
        'monetization_approved_at',
        'is_public',
        'status',
        'scheduled_publish_at',
        'is_sold',
        'sale_mode',
        'grace_period_days',
        'relist_price_multiplier',
        'ecosystem_category',
        'is_draft',
        'scheduled_at',
        'published_at',
        // Code ecosystem fields
        'code_language',
        'code_framework',
        'code_type',
        // Photo ecosystem fields
        'photo_resolution',
        'photo_type',
        'photo_format',
        // Design ecosystem fields
        'design_type',
        'design_format',
        // Audio ecosystem fields
        'audio_duration',
        'audio_format',
        'audio_genre',
        // Video ecosystem fields
        'video_duration',
        'video_resolution',
        'video_format',
        'video_link',
        // Theme ecosystem fields
        'theme_platform',
        'theme_type',
        'theme_preview_link',
        // 3D ecosystem fields
        'three_d_format',
        'three_d_type',
        'three_d_preview_link',
        // Audio ecosystem fields
        'audio_link',
        // Design ecosystem fields
        'design_preview_link',
        // Photo ecosystem fields
        'photo_gallery_link',
        // Code ecosystem fields
        'code_demo_link',
        // General demo link (for apps/software)
        'demo_link',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'monetization_approved' => 'boolean',
            'monetization_auto_approved' => 'boolean',
            'monetization_approved_at' => 'datetime',
            'is_public' => 'boolean',
            'status' => 'string',
            'language' => 'string',
            'scheduled_publish_at' => 'datetime',
            'is_sold' => 'boolean',
            'attachments' => 'array',
            'thumbnails' => 'array',
            'file_count' => 'integer',
            'preview_percentage' => 'integer',
            'notification_meta' => 'array',
            'grace_period_days' => 'integer',
            'relist_price_multiplier' => 'decimal:2',
            'ecosystem_category' => 'string',
            'is_draft' => 'boolean',
            'scheduled_at' => 'datetime',
            'published_at' => 'datetime',
            'series_order' => 'integer',
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

    public function viewRevenues()
    {
        return $this->hasMany(NoteViewRevenue::class);
    }

    /**
     * Get the admin who approved monetization.
     */
    public function monetizationApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'monetization_approved_by');
    }

    /**
     * Check if note can be monetized (free note with approval).
     */
    public function canMonetize(): bool
    {
        // Must be free note
        if ($this->price > 0) {
            return false;
        }

        // Must be approved (either by admin or auto-approved)
        return $this->monetization_approved || $this->monetization_auto_approved;
    }

    /**
     * Check if note has at least one successful sale.
     */
    public function hasSuccessfulSale(): bool
    {
        return $this->transactions()
            ->where('status', 'success')
            ->exists();
    }

    /**
     * Auto-approve monetization if seller has at least 1 successful sale.
     */
    public function checkAndAutoApproveMonetization(): bool
    {
        // Only for free notes
        if ($this->price > 0) {
            return false;
        }

        // Already approved
        if ($this->monetization_approved || $this->monetization_auto_approved) {
            return false;
        }

        // Check if seller has at least 1 successful sale (any note)
        $sellerHasSale = \App\Models\Transaction::where('seller_id', $this->user_id)
            ->where('status', 'success')
            ->exists();

        if ($sellerHasSale) {
            $this->update([
                'monetization_auto_approved' => true,
                'monetization_approved' => true,
                'monetization_approved_at' => now(),
            ]);
            return true;
        }

        return false;
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
     * Get the embedding for this note.
     */
    public function embedding()
    {
        return $this->hasOne(NoteEmbedding::class);
    }

    public function histories()
    {
        return $this->hasMany(NoteHistory::class)->latest();
    }

    public function virusScans()
    {
        return $this->hasMany(VirusScan::class);
    }

    public function watermarkSetting()
    {
        return $this->hasOne(WatermarkSetting::class);
    }

    public function drmSetting()
    {
        return $this->hasOne(DrmSetting::class);
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
     * Get the series that contains this note.
     */
    public function series(): BelongsTo
    {
        return $this->belongsTo(NoteSeries::class, 'series_id');
    }

    /**
     * Get comments for this note.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(NoteComment::class)->whereNull('parent_id')->latest();
    }

    /**
     * Get all comments including replies.
     */
    public function allComments(): HasMany
    {
        return $this->hasMany(NoteComment::class)->latest();
    }

    /**
     * Get reactions for this note.
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(NoteReaction::class);
    }

    /**
     * Get questions for this note.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(NoteQuestion::class)->latest();
    }

    /**
     * Get refunds for this note.
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Get gift notes for this note.
     */
    public function giftNotes(): HasMany
    {
        return $this->hasMany(GiftNote::class);
    }

    /**
     * Get view history for this note.
     */
    public function viewHistory(): HasMany
    {
        return $this->hasMany(NoteViewHistory::class);
    }

    /**
     * Get views count in the last 24 hours.
     */
    public function getViews24HoursAttribute(): int
    {
        return $this->viewHistory()
            ->where('viewed_at', '>=', now()->subHours(24))
            ->count();
    }

    /**
     * Get views count in the last 7 days.
     */
    public function getViews7DaysAttribute(): int
    {
        return $this->viewHistory()
            ->where('viewed_at', '>=', now()->subDays(7))
            ->count();
    }

    /**
     * Check if note is "hot" (high views in last 24 hours).
     */
    public function isHot(): bool
    {
        $hotThreshold = \App\Models\Setting::getSetting('hot_note_threshold', 'marketplace', 50);
        return $this->views_24_hours >= $hotThreshold;
    }

    /**
     * Check if note is "viral" (very high views in last 24 hours or high growth rate).
     */
    public function isViral(): bool
    {
        $viralThreshold = \App\Models\Setting::getSetting('viral_note_threshold', 'marketplace', 200);
        $views24h = $this->views_24_hours;

        // Check if views in 24h exceed viral threshold
        if ($views24h >= $viralThreshold) {
            return true;
        }

        // Check growth rate: if 24h views are more than 50% of 7-day views, it's viral
        $views7d = $this->views_7_days;
        if ($views7d > 0 && $views24h > 0) {
            $growthRate = ($views24h / $views7d) * 100;
            $minGrowthRate = \App\Models\Setting::getSetting('viral_growth_rate_threshold', 'marketplace', 50);
            if ($growthRate >= $minGrowthRate && $views24h >= ($viralThreshold * 0.5)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get viral/hot status badge text.
     */
    public function getViralBadgeTextAttribute(): ?string
    {
        if ($this->isViral()) {
            return 'Viral';
        } elseif ($this->isHot()) {
            return 'Hot';
        }
        return null;
    }

    /**
     * Get bundles that include this note.
     */
    public function bundles()
    {
        return $this->belongsToMany(NoteBundle::class, 'note_bundle_items', 'note_id', 'bundle_id');
    }

    /**
     * Get categories for this note.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'note_category', 'note_id', 'category_id');
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
     * Check if note has video preview.
     */
    public function hasVideoPreview(): bool
    {
        return !empty($this->video_preview);
    }

    /**
     * Get video preview URL.
     */
    public function getVideoPreviewUrlAttribute(): ?string
    {
        if (!$this->hasVideoPreview()) {
            return null;
        }
        return Storage::url($this->video_preview);
    }

    /**
     * Get video preview thumbnail URL.
     */
    public function getVideoPreviewThumbnailUrlAttribute(): ?string
    {
        if (empty($this->video_preview_thumbnail)) {
            return null;
        }
        return Storage::url($this->video_preview_thumbnail);
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

    public function notificationMeta(?string $key = null, $default = null)
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
     * Get share referrals for this note.
     */
    public function shareReferrals()
    {
        return $this->hasMany(NoteShareReferral::class);
    }

    /**
     * Get AI analyses.
     * @deprecated AI features have been removed. This relationship is kept for backward compatibility.
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

    public function reports(): HasMany
    {
        return $this->hasMany(NoteReport::class);
    }

    /**
     * Get contest entries for this note
     */
    public function contestEntries(): HasMany
    {
        return $this->hasMany(\App\Models\ContestEntry::class);
    }

    /**
     * Get subscriptions for this note
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(\App\Models\NoteSubscription::class);
    }

    /**
     * Get collaborators for this note.
     */
    public function collaborators(): HasMany
    {
        return $this->hasMany(NoteCollaborator::class);
    }

    /**
     * Get all authors (including owner and collaborators with author role).
     */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'note_collaborators', 'note_id', 'user_id')
            ->wherePivot('role', 'author')
            ->withTimestamps();
    }

    /**
     * Get versions for this note.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(NoteVersion::class)->orderBy('version_number', 'desc');
    }

    /**
     * Get current version for this note.
     */
    public function currentVersion(): HasMany
    {
        return $this->hasMany(NoteVersion::class)->where('is_current', true);
    }

    /**
     * Get collaboration comments for this note.
     */
    public function collaborationComments(): HasMany
    {
        return $this->hasMany(NoteCollaborationComment::class)->whereNull('parent_id')->latest();
    }

    /**
     * Get all collaboration comments including replies.
     */
    public function allCollaborationComments(): HasMany
    {
        return $this->hasMany(NoteCollaborationComment::class)->latest();
    }

    /**
     * Get active collaboration sessions for this note.
     */
    public function activeCollaborationSessions(): HasMany
    {
        return $this->hasMany(NoteCollaborationSession::class)->where('is_active', true);
    }

    /**
     * Check if user is a collaborator on this note.
     */
    public function isCollaborator(string $userId): bool
    {
        return $this->collaborators()->where('user_id', $userId)->exists();
    }

    /**
     * Check if user can edit this note (owner or collaborator with edit permission).
     */
    public function canUserEdit(string $userId): bool
    {
        // Owner can always edit
        if ($this->user_id === $userId) {
            return true;
        }

        // Check collaborator permissions
        $collaborator = $this->collaborators()->where('user_id', $userId)->first();
        return $collaborator && $collaborator->can_edit;
    }

    /**
     * Get all users who can edit this note.
     */
    public function getEditorsAttribute(): \Illuminate\Support\Collection
    {
        $editors = collect([$this->user]); // Owner is always an editor

        $collaborators = $this->collaborators()
            ->where('can_edit', true)
            ->with('user')
            ->get()
            ->pluck('user');

        return $editors->merge($collaborators)->unique('id');
    }

    /**
     * Get active subscriptions for this note
     */
    public function activeSubscriptions(): HasMany
    {
        return $this->hasMany(\App\Models\NoteSubscription::class)
            ->where('status', 'active')
            ->where('current_period_end', '>', now());
    }

    /**
     * Check if note supports subscriptions
     */
    public function supportsSubscriptions(): bool
    {
        // Can be configured per note or globally
        return $this->is_public && $this->status === 'active';
    }

    /**
     * Check if a user has purchased this note.
     */
    public function isPurchasedBy($userId): bool
    {
        return $this->purchasedBy()->where('user_id', $userId)->exists();
    }

    /**
     * Check if note is in scarcity mode.
     */
    public function isScarcityMode(): bool
    {
        return $this->sale_mode === 'scarcity';
    }

    /**
     * Check if note is in standard mode.
     */
    public function isStandardMode(): bool
    {
        return $this->sale_mode === 'standard';
    }

    /**
     * Check if user can repurchase this note (within grace period or after).
     */
    public function canRepurchase($userId): bool
    {
        if (!$this->isScarcityMode()) {
            return false; // Only scarcity mode supports repurchase
        }

        $transaction = Transaction::where('buyer_id', $userId)
            ->where('note_id', $this->id)
            ->where('status', 'success')
            ->first();

        if (!$transaction) {
            return false; // User never purchased
        }

        // Check if user sold the note
        if ($this->user_id !== $userId) {
            // User sold it, check grace period
            if ($transaction->grace_period_ends_at && $transaction->grace_period_ends_at->isFuture()) {
                return true; // Within grace period - can repurchase at original price
            }
            // After grace period - can repurchase at premium price
            return true;
        }

        return false; // User still owns it
    }

    /**
     * Get repurchase price for user (original price within grace period, premium after).
     */
    public function getRepurchasePrice($userId): ?float
    {
        if (!$this->canRepurchase($userId)) {
            return null;
        }

        $transaction = Transaction::where('buyer_id', $userId)
            ->where('note_id', $this->id)
            ->where('status', 'success')
            ->first();

        if (!$transaction) {
            return null;
        }

        $basePrice = $this->hasDiscount() ? $this->discount_price : $this->price;

        // Check grace period
        if ($transaction->grace_period_ends_at && $transaction->grace_period_ends_at->isFuture()) {
            return (float) $basePrice; // Original price within grace period
        }

        // After grace period - premium price
        return (float) ($basePrice * $this->relist_price_multiplier);
    }
}
