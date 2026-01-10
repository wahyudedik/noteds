<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class BookmarkTag extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Create a new Eloquent model instance.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (!isset($this->attributes['id'])) {
            $this->attributes['id'] = (string) Str::uuid();
        }
    }

    protected $fillable = [
        'name',
        'slug',
        'user_id',
        'is_global',
        'usage_count',
    ];

    protected function casts(): array
    {
        return [
            'is_global' => 'boolean',
            'usage_count' => 'integer',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    /**
     * Get the user that owns this tag (if user-specific).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get bookmarks with this tag.
     */
    public function bookmarks(): BelongsToMany
    {
        return $this->belongsToMany(Bookmark::class, 'bookmark_tag');
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Decrement usage count.
     */
    public function decrementUsage(): void
    {
        $this->decrement('usage_count');
    }

    /**
     * Make tag global.
     */
    public function makeGlobal(): void
    {
        $this->update([
            'is_global' => true,
            'user_id' => null,
        ]);
    }

    /**
     * Make tag private (user-specific).
     */
    public function makePrivate(User $user): void
    {
        $this->update([
            'is_global' => false,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Scope for global tags.
     */
    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }

    /**
     * Scope for user-specific tags.
     */
    public function scopeUserSpecific($query)
    {
        return $query->where('is_global', false);
    }

    /**
     * Scope for popular tags.
     */
    public function scopePopular($query, int $limit = 10)
    {
        return $query->orderBy('usage_count', 'desc')->limit($limit);
    }

    /**
     * Scope for user tags.
     */
    public function scopeForUser($query, ?User $user = null)
    {
        if (!$user) {
            return $query->global();
        }

        return $query->where(function ($q) use ($user) {
            $q->where('is_global', true)
              ->orWhere('user_id', $user->id);
        });
    }
}
