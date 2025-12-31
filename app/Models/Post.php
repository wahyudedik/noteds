<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    protected $fillable = [
        'user_id',
        'campaign_id',
        'purpose_type',
        'title',
        'content',
        'is_validated_post',
        'upvotes_count',
        'downvotes_count',
        'comments_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_validated_post' => 'boolean',
            'upvotes_count' => 'integer',
            'downvotes_count' => 'integer',
            'comments_count' => 'integer',
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
     * Get the campaign associated with this post (if shared from campaign).
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Campaign::class);
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
}
