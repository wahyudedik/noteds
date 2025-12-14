<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RecommendationImpression extends Model
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
        'context',
        'algorithm',
        'position',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'position' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Common recommendation contexts.
     */
    const CONTEXT_HOMEPAGE = 'homepage';
    const CONTEXT_MARKETPLACE = 'marketplace';
    const CONTEXT_SIMILAR_NOTES = 'similar_notes';
    const CONTEXT_PROFILE = 'profile';
    const CONTEXT_SEARCH_RESULTS = 'search_results';
    const CONTEXT_CATEGORY_PAGE = 'category_page';

    /**
     * Get the user that viewed the impression.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the note that was shown.
     */
    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    /**
     * Get the click associated with this impression.
     */
    public function click(): HasOne
    {
        return $this->hasOne(RecommendationClick::class, 'impression_id');
    }

    /**
     * Check if this impression was clicked.
     */
    public function wasClicked(): bool
    {
        return $this->click()->exists();
    }

    /**
     * Scope to filter by context.
     */
    public function scopeByContext($query, string $context)
    {
        return $query->where('context', $context);
    }

    /**
     * Scope to get impressions for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get recent impressions.
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
