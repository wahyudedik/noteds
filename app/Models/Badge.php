<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Badge extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'category',
        'criteria_type',
        'criteria_value',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'criteria_value' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('earned_at', 'notes')
            ->withTimestamps()
            ->orderByPivot('earned_at', 'desc');
    }

    public function userBadges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    /**
     * Check if user has this badge.
     */
    public function hasUser(User $user): bool
    {
        return $this->users()->where('users.id', $user->id)->exists();
    }
}
