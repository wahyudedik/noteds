<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecommendationClick extends Model
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
        'impression_id',
        'user_id',
        'note_id',
        'context',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the impression that led to this click.
     */
    public function impression(): BelongsTo
    {
        return $this->belongsTo(RecommendationImpression::class);
    }

    /**
     * Get the user that clicked.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the note that was clicked.
     */
    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    /**
     * Scope to filter by context.
     */
    public function scopeByContext($query, string $context)
    {
        return $query->where('context', $context);
    }

    /**
     * Scope to get clicks for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get recent clicks.
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Calculate click-through rate for a specific context.
     */
    public static function calculateCTR(string $context, int $days = 7): float
    {
        $impressions = RecommendationImpression::byContext($context)
            ->recent($days)
            ->count();

        if ($impressions === 0) {
            return 0.0;
        }

        $clicks = static::byContext($context)
            ->recent($days)
            ->count();

        return ($clicks / $impressions) * 100;
    }
}
