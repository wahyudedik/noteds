<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NoteCollaborationComment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'note_id',
        'user_id',
        'parent_id',
        'content',
        'target_type',
        'target_reference',
        'target_position',
        'status',
        'resolved_by',
        'resolved_at',
        'is_edited',
    ];

    protected function casts(): array
    {
        return [
            'target_position' => 'array',
            'resolved_at' => 'datetime',
            'is_edited' => 'boolean',
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(NoteCollaborationComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(NoteCollaborationComment::class, 'parent_id')->latest();
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Mark comment as resolved.
     */
    public function resolve(?string $userId = null): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_by' => $userId ?? auth()->id(),
            'resolved_at' => now(),
        ]);
    }

    /**
     * Mark comment as open.
     */
    public function reopen(): void
    {
        $this->update([
            'status' => 'open',
            'resolved_by' => null,
            'resolved_at' => null,
        ]);
    }

    /**
     * Check if comment is resolved.
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }
}
