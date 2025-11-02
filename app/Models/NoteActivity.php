<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteActivity extends Model
{
    use HasUuids;

    protected $fillable = [
        'note_id',
        'user_id',
        'action',
        'description',
        'changes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'metadata' => 'array',
        ];
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get human-readable action description.
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'published' => 'Published',
            'unpublished' => 'Unpublished',
            'tagged' => 'Tagged',
            'untagged' => 'Untagged',
            'file_added' => 'File Added',
            'file_removed' => 'File Removed',
            default => ucfirst($this->action),
        };
    }
}

