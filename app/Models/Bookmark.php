<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Bookmark extends Model
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
        'post_id',
        'collection_id',
        'notes',
    ];

    /**
     * Get the user who bookmarked the post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the post that was bookmarked.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the collection this bookmark belongs to.
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(BookmarkCollection::class);
    }

    /**
     * Get tags for this bookmark.
     */
    public function tags(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(BookmarkTag::class, 'bookmark_tag', 'bookmark_id', 'tag_id')
            ->withPivot('id')
            ->withTimestamps();
    }

    /**
     * Move bookmark to a collection.
     */
    public function moveToCollection(?BookmarkCollection $collection): void
    {
        $this->collection_id = $collection?->id;
        $this->save();
    }

    /**
     * Remove bookmark from collection.
     */
    public function removeFromCollection(): void
    {
        $this->collection_id = null;
        $this->save();
    }

    /**
     * Add tag to bookmark.
     */
    public function addTag(BookmarkTag $tag): void
    {
        if (!$this->tags()->where('bookmark_tags.id', $tag->id)->exists()) {
            $this->tags()->attach($tag->id, ['id' => (string) Str::uuid()]);
            $tag->incrementUsage();
        }
    }

    /**
     * Remove tag from bookmark.
     */
    public function removeTag(BookmarkTag $tag): void
    {
        if ($this->tags()->where('bookmark_tags.id', $tag->id)->exists()) {
            $this->tags()->detach($tag->id);
            $tag->decrementUsage();
        }
    }

    /**
     * Sync tags for bookmark.
     */
    public function syncTags(array $tagIds): void
    {
        $oldTagIds = $this->tags()->pluck('bookmark_tags.id')->toArray();
        $removedTagIds = array_diff($oldTagIds, $tagIds);
        $addedTagIds = array_diff($tagIds, $oldTagIds);

        foreach ($removedTagIds as $tagId) {
            $tag = BookmarkTag::find($tagId);
            if ($tag) {
                $tag->decrementUsage();
            }
        }

        foreach ($addedTagIds as $tagId) {
            $tag = BookmarkTag::find($tagId);
            if ($tag) {
                $tag->incrementUsage();
            }
        }

        if (!empty($removedTagIds)) {
            $this->tags()->detach($removedTagIds);
        }

        foreach ($addedTagIds as $tagId) {
            $this->tags()->attach($tagId, ['id' => (string) Str::uuid()]);
        }
    }

    /**
     * Check if bookmark has notes.
     */
    public function hasNotes(): bool
    {
        return !empty($this->notes);
    }

    /**
     * Get notes preview (first 100 characters).
     */
    public function getNotesPreviewAttribute(): ?string
    {
        if (!$this->notes) {
            return null;
        }

        return Str::limit(strip_tags($this->notes), 100);
    }

    protected function casts(): array
    {
        return [
            'notes_updated_at' => 'datetime',
        ];
    }
}
