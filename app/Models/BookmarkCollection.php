<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BookmarkCollection extends Model
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
        'user_id',
        'parent_id',
        'name',
        'description',
        'icon',
        'color',
        'sort_order',
        'is_default',
        'is_public',
        'public_slug',
        'share_settings',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_default' => 'boolean',
            'is_public' => 'boolean',
            'share_settings' => 'array',
        ];
    }

    /**
     * Get the user that owns this collection.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent collection.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(BookmarkCollection::class, 'parent_id');
    }

    /**
     * Get child collections.
     */
    public function children(): HasMany
    {
        return $this->hasMany(BookmarkCollection::class, 'parent_id')->ordered();
    }

    /**
     * Get bookmarks in this collection.
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Get shares for this collection.
     */
    public function shares(): HasMany
    {
        return $this->hasMany(BookmarkCollectionShare::class);
    }

    /**
     * Get users this collection is shared with.
     */
    public function sharedWith(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bookmark_collection_shares', 'collection_id', 'shared_with_user_id')
            ->withPivot('permission', 'accepted_at')
            ->withTimestamps();
    }

    /**
     * Check if collection is root (no parent).
     */
    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * Get collection path (breadcrumb).
     */
    public function getPath(): array
    {
        $path = [];
        $current = $this;

        while ($current) {
            array_unshift($path, $current);
            $current = $current->parent;
        }

        return $path;
    }

    /**
     * Get collection depth (0 for root).
     */
    public function getDepth(): int
    {
        return count($this->getPath()) - 1;
    }

    /**
     * Check if collection can be deleted.
     */
    public function canBeDeleted(): bool
    {
        // Cannot delete if it has bookmarks or children
        return $this->bookmarks()->count() === 0 && $this->children()->count() === 0;
    }

    /**
     * Generate public slug.
     */
    public function generatePublicSlug(): string
    {
        $baseSlug = Str::slug($this->name);
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('public_slug', $slug)->where('id', '!=', $this->id)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get public URL.
     */
    public function getPublicUrlAttribute(): ?string
    {
        if (!$this->is_public || !$this->public_slug) {
            return null;
        }

        return route('bookmarks.collections.public', $this->public_slug);
    }

    /**
     * Check if collection is shared with user.
     */
    public function isSharedWith(User $user): bool
    {
        return $this->shares()
            ->where('shared_with_user_id', $user->id)
            ->whereNotNull('accepted_at')
            ->exists();
    }

    /**
     * Check if user can view this collection.
     */
    public function canUserView(?User $user): bool
    {
        // Owner can always view
        if ($user && $this->user_id === $user->id) {
            return true;
        }

        // Public collections can be viewed by anyone
        if ($this->is_public) {
            return true;
        }

        // Check if shared with user
        if ($user && $this->isSharedWith($user)) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can edit this collection.
     */
    public function canUserEdit(?User $user): bool
    {
        // Owner can always edit
        if ($user && $this->user_id === $user->id) {
            return true;
        }

        // Check if shared with edit permission
        if ($user) {
            $share = $this->shares()
                ->where('shared_with_user_id', $user->id)
                ->whereNotNull('accepted_at')
                ->where('permission', 'edit')
                ->first();

            return $share !== null;
        }

        return false;
    }

    /**
     * Scope for root collections.
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope for user collections.
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Scope for ordered collections.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }
}
