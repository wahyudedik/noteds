<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatRating extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'conversation_id',
        'rater_id',
        'rated_user_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(NoteConversation::class);
    }

    public function rater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    public function ratedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rated_user_id');
    }

    /**
     * Check if user has already rated a conversation.
     */
    public static function hasRated(string $conversationId, string $userId): bool
    {
        return static::where('conversation_id', $conversationId)
            ->where('rater_id', $userId)
            ->exists();
    }

    /**
     * Get average rating for a user.
     */
    public static function getAverageRating(string $userId): float
    {
        return static::where('rated_user_id', $userId)
            ->avg('rating') ?? 0.0;
    }

    /**
     * Get rating count for a user.
     */
    public static function getRatingCount(string $userId): int
    {
        return static::where('rated_user_id', $userId)
            ->count();
    }
}
