<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CallSession extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'call_sessions';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'conversation_id', 'host_user_id', 'is_active', 'started_at', 'ended_at',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(CallParticipant::class);
    }
}
