<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Hashtag;
use App\Models\PostReport;
use App\Models\User;
use App\Models\PostView;
use Illuminate\Support\Facades\DB;

class Post extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'content',
        'note_id',
        'parent_id',
        'is_pinned',
        'is_hidden',
        'hidden_at',
        'is_published',
        'scheduled_at',
        'published_at',
        'visibility',
        'likes_count',
        'comments_count',
        'shares_count',
        'views_count',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'is_hidden' => 'boolean',
            'hidden_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'published_at' => 'datetime',
            'visibility' => 'string',
            'likes_count' => 'integer',
            'comments_count' => 'integer',
            'shares_count' => 'integer',
            'views_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Post::class, 'parent_id')->latest();
    }

    public function likes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_likes', 'post_id', 'user_id')
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class)->whereNull('parent_id')->latest();
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(PostComment::class)->latest();
    }

    public function media(): HasMany
    {
        return $this->hasMany(PostMedia::class)->orderBy('order');
    }

    public function hashtags(): BelongsToMany
    {
        return $this->belongsToMany(Hashtag::class, 'post_hashtags', 'post_id', 'hashtag_id')
            ->withTimestamps();
    }

    public function mentions(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_mentions', 'post_id', 'user_id')
            ->withTimestamps();
    }

    public function bookmarks(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_bookmarks', 'post_id', 'user_id')
            ->withTimestamps();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(PostReport::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(PostView::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            });
    }

    public function scopeVisibleTo($query, ?User $user)
    {
        if ($user && $user->hasRole('admin')) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where(function ($public) {
                $public->where('visibility', 'public')
                    ->where('is_published', true)
                    ->where(function ($schedule) {
                        $schedule->whereNull('scheduled_at')
                            ->orWhere('scheduled_at', '<=', now());
                    });
            });

            if ($user) {
                $q->orWhere(function ($owner) use ($user) {
                    $owner->where('user_id', $user->id);
                });

                $q->orWhere(function ($followers) use ($user) {
                    $followers->where('visibility', 'followers')
                        ->where('is_published', true)
                        ->where(function ($schedule) {
                            $schedule->whereNull('scheduled_at')
                                ->orWhere('scheduled_at', '<=', now());
                        })
                        ->whereExists(function ($sub) use ($user) {
                            $sub->select(DB::raw(1))
                                ->from('follows')
                                ->whereColumn('follows.following_id', 'posts.user_id')
                                ->where('follows.follower_id', $user->id);
                        });
                });
            }
        });
    }

    public function canBeViewedBy(?User $user): bool
    {
        if ($user && $user->hasRole('admin')) {
            return true;
        }

        if (!$this->is_published) {
            return $user && $user->id === $this->user_id;
        }

        if ($this->scheduled_at && $this->scheduled_at->isFuture()) {
            return $user && $user->id === $this->user_id;
        }

        if ($this->is_hidden) {
            return $user && $user->id === $this->user_id;
        }

        if ($this->visibility === 'public') {
            return true;
        }

        if (!$user) {
            return false;
        }

        if ($user->id === $this->user_id) {
            return true;
        }

        if ($this->visibility === 'followers') {
            return $user->following()->where('following_id', $this->user_id)->exists();
        }

        return false;
    }

    /**
     * Check if user has liked this post
     */
    public function isLikedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if user has bookmarked this post
     */
    public function isBookmarkedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        return $this->bookmarks()->where('user_id', $user->id)->exists();
    }

    /**
     * Get shareable URL for this post
     */
    public function getShareUrlAttribute(): string
    {
        return route('forum.show', $this->id);
    }
}

