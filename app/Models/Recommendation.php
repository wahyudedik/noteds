<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recommendation extends Model
{
    use HasFactory;

    /**
     * Disable updated_at timestamp (we only need created_at).
     */
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'note_id',
        'algorithm',
        'score',
        'metadata',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'score' => 'decimal:4',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Available recommendation algorithms.
     */
    const ALGORITHM_COLLABORATIVE = 'collaborative';
    const ALGORITHM_CONTENT_BASED = 'content_based';
    const ALGORITHM_TRENDING = 'trending';
    const ALGORITHM_PROFILE_BASED = 'profile_based';

    /**
     * Get the user that owns the recommendation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the note being recommended.
     */
    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    /**
     * Scope to filter by algorithm.
     */
    public function scopeByAlgorithm($query, string $algorithm)
    {
        return $query->where('algorithm', $algorithm);
    }

    /**
     * Scope to get top recommendations by score.
     */
    public function scopeTopScored($query, int $limit = 10)
    {
        return $query->orderBy('score', 'desc')->limit($limit);
    }

    /**
     * Scope to get recent recommendations.
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
