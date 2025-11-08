<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $workspace_id
 * @property string|null $user_id
 * @property string $action
 * @property array|null $metadata
 */

class WorkspaceActivityLog extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'workspace_id',
        'user_id',
        'action',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(Workspace $workspace, string $action, ?User $user = null, array $metadata = []): self
    {
        return self::create([
            'workspace_id' => $workspace->id,
            'user_id' => $user?->id,
            'action' => $action,
            'metadata' => $metadata ?: null,
        ]);
    }
}


