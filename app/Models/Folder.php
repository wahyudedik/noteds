<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folder extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'workspace_id',
        'parent_id',
        'name',
        'color',
        'description',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    /**
     * Get the user that owns the folder.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent folder.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    /**
     * Get child folders.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id')->orderBy('order');
    }

    /**
     * Get notes in this folder.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class)->latest();
    }

    /**
     * Get the workspace that contains this folder.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get all descendant folders (recursive).
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Check if folder has children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Get notes count including subfolders.
     */
    public function getTotalNotesCountAttribute(): int
    {
        $count = $this->notes()->count();
        
        foreach ($this->children as $child) {
            $count += $child->total_notes_count;
        }
        
        return $count;
    }
}
