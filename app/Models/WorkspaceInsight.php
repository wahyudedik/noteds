<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceInsight extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'workspace_id',
        'type',
        'category',
        'title',
        'description',
        'data',
        'severity',
        'is_read',
        'created_for_user_id',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_read' => 'boolean',
            'generated_at' => 'datetime',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function createdForUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_for_user_id');
    }

    /**
     * Mark insight as read
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }
}

