<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Comment extends Model
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
        'parent_id',
        'content',
        'upvotes_count',
        'downvotes_count',
        'weighted_upvotes_score',
        'weighted_downvotes_score',
        'is_best_answer',
        'edited_at',
        'edit_count',
        'is_pinned',
        'pinned_at',
    ];

    protected function casts(): array
    {
        return [
            'is_best_answer' => 'boolean',
            'upvotes_count' => 'integer',
            'downvotes_count' => 'integer',
            'weighted_upvotes_score' => 'decimal:2',
            'weighted_downvotes_score' => 'decimal:2',
            'edited_at' => 'datetime',
            'edit_count' => 'integer',
            'is_pinned' => 'boolean',
            'pinned_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(CommentVote::class);
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
     * Get mentions in this comment.
     */
    public function mentions(): HasMany
    {
        return $this->hasMany(CommentMention::class);
    }

    /**
     * Get media attachments for this comment.
     */
    public function media(): HasMany
    {
        return $this->hasMany(CommentMedia::class)->orderBy('order');
    }

    /**
     * Get reactions for this comment.
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(CommentReaction::class);
    }

    /**
     * Get reaction count for a specific emoji.
     */
    public function getReactionCount(string $emoji): int
    {
        $reaction = $this->reactions()->where('emoji', $emoji)->first();
        return $reaction ? $reaction->count : 0;
    }

    /**
     * Get edit history for this comment.
     */
    public function editHistory(): HasMany
    {
        return $this->hasMany(CommentEditHistory::class)->orderBy('edited_at', 'desc');
    }

    /**
     * Scope to get pinned comments.
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Scope to get non-pinned comments.
     */
    public function scopeNotPinned($query)
    {
        return $query->where('is_pinned', false);
    }

    /**
     * Check if comment is pinned.
     */
    public function isPinned(): bool
    {
        return $this->is_pinned === true;
    }
}
