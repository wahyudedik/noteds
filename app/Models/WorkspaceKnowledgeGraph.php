<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceKnowledgeGraph extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'workspace_knowledge_graph';

    protected $fillable = [
        'workspace_id',
        'entity_type',
        'entity_id',
        'entity_name',
        'relationships',
        'properties',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'relationships' => 'array',
            'properties' => 'array',
            'tags' => 'array',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}

