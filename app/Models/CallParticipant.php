<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'call_session_id', 'user_id', 'is_muted', 'video_enabled', 'joined_at', 'left_at',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(CallSession::class, 'call_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
