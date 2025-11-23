<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceMember extends Model
{
    use HasUuids;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'role',
        'is_active',
        'joined_at',
        'permissions',
        'team_role',
        'can_manage_members',
        'can_manage_workspace',
        'can_create_notes',
        'can_edit_notes',
        'can_delete_notes',
        'can_manage_folders',
        'can_invite_members',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'joined_at' => 'datetime',
            'permissions' => 'array',
            'can_manage_members' => 'boolean',
            'can_manage_workspace' => 'boolean',
            'can_create_notes' => 'boolean',
            'can_edit_notes' => 'boolean',
            'can_delete_notes' => 'boolean',
            'can_manage_folders' => 'boolean',
            'can_invite_members' => 'boolean',
        ];
    }

    /**
     * Get the workspace.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
