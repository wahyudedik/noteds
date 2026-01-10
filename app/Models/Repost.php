<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Repost extends Model
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
        'user_id',
        'post_id',
        'comment',
        'is_quote_repost',
        'quote_content',
        'quote_post_id',
        'display_mode',
    ];

    /**
     * Store post_id before deletion for use in deleted event.
     */
    protected $postIdBeforeDelete = null;

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Auto-increment reposts_count when repost is created
        static::created(function ($repost) {
            // Use post_id directly to avoid relationship loading issues
            if ($repost->post_id) {
                Post::where('id', $repost->post_id)->increment('reposts_count');
            }

            // Fire event for analytics tracking
            event(new \App\Events\PostReposted($repost));
        });

        // Auto-decrement reposts_count when repost is deleted
        static::deleting(function ($repost) {
            // Store post_id before deletion
            $repost->postIdBeforeDelete = $repost->post_id;
        });

        static::deleted(function ($repost) {
            // Use stored post_id to decrement count
            if ($repost->postIdBeforeDelete) {
                Post::where('id', $repost->postIdBeforeDelete)->decrement('reposts_count');
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Check if repost has comment.
     */
    public function hasComment(): bool
    {
        return !empty($this->comment);
    }

    /**
     * Get comment preview (first 100 characters).
     */
    public function getCommentPreviewAttribute(): ?string
    {
        if (!$this->comment) {
            return null;
        }

        return \Illuminate\Support\Str::limit($this->comment, 100);
    }

    /**
     * Update repost comment.
     */
    public function updateComment(?string $comment): void
    {
        $this->update([
            'comment' => $comment,
            'comment_updated_at' => $comment ? now() : null,
        ]);
    }

    /**
     * Get the quote post (if this is a quote repost with separate display).
     */
    public function quotePost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'quote_post_id');
    }

    /**
     * Check if this is a quote repost.
     */
    public function isQuoteRepost(): bool
    {
        return $this->is_quote_repost === true;
    }

    /**
     * Get display mode.
     */
    public function getDisplayMode(): string
    {
        return $this->display_mode ?? 'embedded';
    }

    protected function casts(): array
    {
        return [
            'comment_updated_at' => 'datetime',
            'is_quote_repost' => 'boolean',
        ];
    }
}

