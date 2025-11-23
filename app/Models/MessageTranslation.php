<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageTranslation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'message_id',
        'target_language',
        'translated_message',
        'provider',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(NoteMessage::class);
    }

    /**
     * Get translation for a message in a specific language.
     */
    public static function getTranslation(string $messageId, string $targetLanguage): ?self
    {
        return static::where('message_id', $messageId)
            ->where('target_language', $targetLanguage)
            ->first();
    }

    /**
     * Create or update translation for a message.
     */
    public static function createOrUpdateTranslation(
        string $messageId,
        string $targetLanguage,
        string $translatedMessage,
        ?string $provider = null
    ): self {
        return static::updateOrCreate(
            [
                'message_id' => $messageId,
                'target_language' => $targetLanguage,
            ],
            [
                'translated_message' => $translatedMessage,
                'provider' => $provider,
            ]
        );
    }
}
