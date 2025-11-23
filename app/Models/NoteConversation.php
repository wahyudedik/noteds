<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class NoteConversation extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'note_id',
        'buyer_id',
        'seller_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(NoteMessage::class, 'conversation_id')->orderBy('created_at');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(NoteMessage::class, 'conversation_id')->latestOfMany();
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(ChatRating::class);
    }

    /**
     * Check if user has rated this conversation.
     */
    public function hasRatingFrom(string $userId): bool
    {
        return $this->ratings()->where('rater_id', $userId)->exists();
    }

    /**
     * Get rating from a specific user.
     */
    public function getRatingFrom(string $userId): ?ChatRating
    {
        return $this->ratings()->where('rater_id', $userId)->first();
    }
}


