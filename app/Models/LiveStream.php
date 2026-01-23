<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiveStream extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'description', 'status',
        'scheduled_at', 'started_at', 'ended_at',
        'provider', 'ingest_url', 'stream_key', 'playback_url',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(LiveChatMessage::class);
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(StreamAnalytics::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(StreamingProvider::class, 'streaming_provider_id');
    }
}
