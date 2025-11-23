<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceReminder extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'task_id',
        'note_id',
        'title',
        'description',
        'remind_at',
        'is_completed',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'remind_at' => 'datetime',
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(WorkspaceTask::class);
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    /**
     * Check if reminder is due
     */
    public function isDue(): bool
    {
        return $this->remind_at->isPast() && !$this->is_completed;
    }
}

