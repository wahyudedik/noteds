<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NoteMessage extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message',
        'original_language',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(NoteConversation::class, 'conversation_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(MessageTranslation::class);
    }

    /**
     * Get translation for a specific language.
     */
    public function getTranslation(string $targetLanguage): ?MessageTranslation
    {
        return $this->translations()
            ->where('target_language', $targetLanguage)
            ->first();
    }
}


