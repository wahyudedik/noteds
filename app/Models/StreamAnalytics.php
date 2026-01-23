<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StreamAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'live_stream_id', 'views_count', 'chat_count', 'duration_seconds',
    ];

    public function stream(): BelongsTo
    {
        return $this->belongsTo(LiveStream::class, 'live_stream_id');
    }
}
