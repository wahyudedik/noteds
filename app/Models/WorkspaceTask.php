<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceTask extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'workspace_id',
        'created_by',
        'assigned_to',
        'note_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at',
        'tags',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'due_date' => 'datetime',
            'completed_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    /**
     * Check if task is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if task is overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date 
            && $this->due_date->isPast() 
            && !$this->isCompleted();
    }
}

