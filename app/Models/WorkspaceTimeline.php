<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceTimeline extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'event_type',
        'title',
        'description',
        'related_id',
        'related_type',
        'metadata',
        'event_date',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'event_date' => 'datetime',
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
}

