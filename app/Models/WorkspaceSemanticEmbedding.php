<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceSemanticEmbedding extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'workspace_id',
        'note_id',
        'content_type',
        'content',
        'embedding',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'embedding' => 'array',
            'metadata' => 'array',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }
}

