<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteCollaborator extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'note_id',
        'user_id',
        'role',
        'can_edit',
        'can_delete',
        'can_invite',
        'invited_at',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'can_edit' => 'boolean',
            'can_delete' => 'boolean',
            'can_invite' => 'boolean',
            'invited_at' => 'datetime',
            'joined_at' => 'datetime',
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
     * Check if collaborator is an author.
     */
    public function isAuthor(): bool
    {
        return $this->role === 'author';
    }

    /**
     * Check if collaborator can edit.
     */
    public function canEdit(): bool
    {
        return $this->can_edit;
    }

    /**
     * Check if collaborator can delete.
     */
    public function canDelete(): bool
    {
        return $this->can_delete;
    }

    /**
     * Check if collaborator can invite others.
     */
    public function canInvite(): bool
    {
        return $this->can_invite;
    }
}
