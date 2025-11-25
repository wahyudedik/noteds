<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'type',
        'subject_id',
        'subject_type',
        'properties',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ActivityLike::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ActivityComment::class)->whereNull('parent_id')->latest();
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(ActivityComment::class)->latest();
    }

    public function shares(): HasMany
    {
        return $this->hasMany(ActivityShare::class);
    }

    /**
     * Check if user has liked this activity
     */
    public function isLikedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->likes()->where('user_id', $user->id)->exists();
    }

    /**
     * Get like count
     */
    public function getLikesCountAttribute(): int
    {
        return $this->likes()->count();
    }

    /**
     * Get comment count
     */
    public function getCommentsCountAttribute(): int
    {
        return $this->allComments()->count();
    }

    /**
     * Get share count
     */
    public function getSharesCountAttribute(): int
    {
        return $this->shares()->count();
    }
}

