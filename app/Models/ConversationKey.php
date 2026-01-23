<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationKey extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'conversation_keys';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'conversation_id', 'version', 'algorithm', 'encrypted_key', 'rotated_at',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
