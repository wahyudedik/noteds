<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Post extends Model
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

    /**
     * Retrieve the model for route model binding.
     * This prevents "home" or other non-UUID strings from being resolved as post IDs.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // Extra safety: ensure value is a valid UUID before attempting to resolve
        // This prevents route model binding from resolving "home" as a post ID
        if (!Str::isUuid($value)) {
            abort(404, 'Post not found.');
        }

        return parent::resolveRouteBinding($value, $field);
    }

    protected $fillable = [
        'user_id',
        'original_post_id',
        'is_quote_repost',
        'purpose_type',
        'business_type',
        'title',
        'content',
        'link_url',
        'link_preview_title',
        'link_preview_description',
        'link_preview_image',
        'link_preview_site_name',
        'is_validated_post',
        'upvotes_count',
        'downvotes_count',
        'weighted_upvotes_score',
        'weighted_downvotes_score',
        'comments_count',
        'reposts_count',
        'status',
        'edited_at',
        'edit_count',
        'trending_score',
        'last_trending_calculated_at',
        'total_views',
        'is_pinned',
        'pinned_at',
        'series_id',
        'series_order',
        'is_series_root',
        'scheduled_at',
        'publish_status',
    ];

    protected function casts(): array
    {
        return [
            'is_validated_post' => 'boolean',
            'upvotes_count' => 'integer',
            'downvotes_count' => 'integer',
            'weighted_upvotes_score' => 'decimal:2',
            'weighted_downvotes_score' => 'decimal:2',
            'comments_count' => 'integer',
            'reposts_count' => 'integer',
            'is_quote_repost' => 'boolean',
            'edited_at' => 'datetime',
            'edit_count' => 'integer',
            'trending_score' => 'decimal:4',
            'last_trending_calculated_at' => 'datetime',
            'total_views' => 'integer',
            'is_pinned' => 'boolean',
            'pinned_at' => 'datetime',
            'is_series_root' => 'boolean',
            'series_order' => 'integer',
            'scheduled_at' => 'datetime',
            'publish_status' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(\App\Models\Comment::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(\App\Models\PostVote::class);
    }

    /**
     * Get weighted score (upvotes - downvotes).
     */
    public function getWeightedScoreAttribute(): float
    {
        return ($this->weighted_upvotes_score ?? 0) - ($this->weighted_downvotes_score ?? 0);
    }

    /**
     * Get simple score (upvotes - downvotes).
     */
    public function getSimpleScoreAttribute(): int
    {
        return ($this->upvotes_count ?? 0) - ($this->downvotes_count ?? 0);
    }

    /**
     * Scope to order by weighted score.
     */
    public function scopeOrderByWeightedScore($query, string $direction = 'desc')
    {
        return $query->orderByRaw('(weighted_upvotes_score - weighted_downvotes_score) ' . $direction);
    }

    /**
     * Scope to order by simple score.
     */
    public function scopeOrderBySimpleScore($query, string $direction = 'desc')
    {
        return $query->orderByRaw('(upvotes_count - downvotes_count) ' . $direction);
    }

    /**
     * Get the users who bookmarked this post.
     */
    public function bookmarkedBy(): HasMany
    {
        return $this->hasMany(\App\Models\Bookmark::class);
    }

    /**
     * Check if the post is bookmarked by a specific user.
     */
    public function isBookmarkedBy(string $userId): bool
    {
        return $this->bookmarkedBy()->where('user_id', $userId)->exists();
    }

    /**
     * Get the media associated with this post.
     */
    public function media(): HasMany
    {
        return $this->hasMany(PostMedia::class)->orderBy('order');
    }

    /**
     * Get link preview data as array.
     */
    public function getLinkPreviewAttribute(): ?array
    {
        if (!$this->link_url) {
            return null;
        }

        return [
            'url' => $this->link_url,
            'title' => $this->link_preview_title,
            'description' => $this->link_preview_description,
            'image' => $this->link_preview_image,
            'site_name' => $this->link_preview_site_name,
        ];
    }

    /**
     * Get all reposts of this post.
     */
    public function reposts(): HasMany
    {
        return $this->hasMany(Repost::class);
    }

    /**
     * Get the users who reposted this post.
     */
    public function repostedBy(): HasMany
    {
        return $this->hasMany(Repost::class);
    }

    /**
     * Check if the post is reposted by a specific user.
     */
    public function isRepostedBy(string $userId): bool
    {
        return $this->reposts()->where('user_id', $userId)->exists();
    }

    /**
     * Get the original post (if this is a quote repost).
     */
    public function originalPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'original_post_id');
    }

    /**
     * Get quote reposts of this post (reposts that created new posts).
     */
    public function quoteReposts(): HasMany
    {
        return $this->hasMany(Repost::class, 'post_id')->where('is_quote_repost', true);
    }

    /**
     * Check if this post is a quote repost.
     */
    public function isQuoteRepost(): bool
    {
        return $this->is_quote_repost === true;
    }

    /**
     * Get the original post.
     */
    public function getOriginalPost(): ?Post
    {
        return $this->originalPost;
    }

    /**
     * Get hashtags associated with this post.
     */
    public function hashtags(): BelongsToMany
    {
        return $this->belongsToMany(Hashtag::class, 'post_hashtag')
            ->withTimestamps();
    }

    /**
     * Get mentions in this post.
     */
    public function mentions(): HasMany
    {
        return $this->hasMany(PostMention::class);
    }

    /**
     * Get poll associated with this post.
     */
    public function poll(): HasOne
    {
        return $this->hasOne(Poll::class);
    }

    /**
     * Get edit history for this post.
     */
    public function editHistory(): HasMany
    {
        return $this->hasMany(PostEditHistory::class)->orderBy('edited_at', 'desc');
    }

    /**
     * Get collaborators for this post.
     */
    public function collaborators(): HasMany
    {
        return $this->hasMany(PostCollaborator::class);
    }

    /**
     * Check if post has been edited.
     */
    public function isEdited(): bool
    {
        return $this->edited_at !== null;
    }

    /**
     * Get analytics for this post.
     */
    public function analytics(): HasMany
    {
        return $this->hasMany(PostAnalytics::class)->orderBy('date', 'desc');
    }

    /**
     * Get the series root post (if this post is part of a series).
     */
    public function seriesRoot(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'series_id');
    }

    /**
     * Get posts in this series (if this is a series root).
     */
    public function seriesPosts(): HasMany
    {
        return $this->hasMany(Post::class, 'series_id')->orderBy('series_order');
    }

    /**
     * Scope to get pinned posts.
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Check if post is pinned.
     */
    public function isPinned(): bool
    {
        return $this->is_pinned === true;
    }

    /**
     * Check if post is part of a series.
     */
    public function isInSeries(): bool
    {
        return $this->series_id !== null;
    }

    /**
     * Check if post is a series root.
     */
    public function isSeriesRoot(): bool
    {
        return $this->is_series_root === true;
    }

    /**
     * Scope to get scheduled posts.
     */
    public function scopeScheduled($query)
    {
        return $query->where('publish_status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now());
    }

    /**
     * Scope to get draft posts.
     */
    public function scopeDraft($query)
    {
        return $query->where('publish_status', 'draft');
    }

    /**
     * Scope to get published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('publish_status', 'published');
    }

    /**
     * Check if post is scheduled.
     */
    public function isScheduled(): bool
    {
        return $this->publish_status === 'scheduled' && $this->scheduled_at !== null;
    }

    /**
     * Check if post is draft.
     */
    public function isDraft(): bool
    {
        return $this->publish_status === 'draft';
    }

    /**
     * Check if post can be published.
     */
    public function canPublish(): bool
    {
        if ($this->isScheduled()) {
            return $this->scheduled_at <= now();
        }
        return $this->isDraft();
    }

    /**
     * Check if user is a collaborator on this post.
     */
    public function isCollaborator(User $user): bool
    {
        return $this->collaborators()->where('user_id', $user->id)->where('status', 'accepted')->exists();
    }

    /**
     * Check if user can edit this post.
     */
    public function canUserEdit(User $user): bool
    {
        // Owner can always edit
        if ($this->user_id === $user->id) {
            return true;
        }

        // Check if user is an accepted collaborator with edit permission
        $collaboration = $this->collaborators()
            ->where('user_id', $user->id)
            ->where('status', 'accepted')
            ->first();

        return $collaboration && $collaboration->can_edit;
    }

    /**
     * Check if user can publish this post.
     */
    public function canUserPublish(User $user): bool
    {
        // Owner can always publish
        if ($this->user_id === $user->id) {
            return true;
        }

        // Check if user is an accepted collaborator with publish permission
        $collaboration = $this->collaborators()
            ->where('user_id', $user->id)
            ->where('status', 'accepted')
            ->first();

        return $collaboration && $collaboration->can_publish;
    }
}
