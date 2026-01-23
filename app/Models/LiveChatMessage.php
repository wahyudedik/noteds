<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'live_stream_id', 'user_id', 'content',
    ];

    public function stream(): BelongsTo
    {
        return $this->belongsTo(LiveStream::class, 'live_stream_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
