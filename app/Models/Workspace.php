<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Workspace extends Model
{
    use HasUuids;

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'type',
        'description',
        'avatar',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($workspace) {
            if (empty($workspace->slug)) {
                $baseSlug = Str::slug($workspace->name);
                $slug = $baseSlug;
                $counter = 1;
                
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                
                $workspace->slug = $slug;
            }
        });
    }

    /**
     * Get the owner of the workspace.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get all members of the workspace.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_members', 'workspace_id', 'user_id')
            ->withPivot('role', 'is_active', 'joined_at')
            ->withTimestamps()
            ->wherePivot('is_active', true);
    }

    /**
     * Get all workspace members (including inactive).
     */
    public function allMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_members', 'workspace_id', 'user_id')
            ->withPivot('role', 'is_active', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Get workspace member records.
     */
    public function memberRecords(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class);
    }

    /**
     * Get notes in this workspace.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class)->latest();
    }

    /**
     * Get folders in this workspace.
     */
    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class)->whereNull('parent_id')->latest();
    }

    /**
     * Check if user is a member of this workspace.
     */
    public function hasMember(User $user): bool
    {
        return $this->members()->where('users.id', $user->id)->exists();
    }

    /**
     * Get user's role in this workspace.
     */
    public function getUserRole(User $user): ?string
    {
        $member = $this->memberRecords()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
        
        if ($user->id === $this->owner_id) {
            return 'owner';
        }
        
        return $member?->role;
    }

    /**
     * Check if user can manage workspace.
     */
    public function canManage(User $user): bool
    {
        if ($user->id === $this->owner_id) {
            return true;
        }
        
        $role = $this->getUserRole($user);
        return in_array($role, ['owner', 'admin']);
    }
}
