<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StreamLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'live_stream_id', 'provider', 'level', 'message', 'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function stream(): BelongsTo
    {
        return $this->belongsTo(LiveStream::class, 'live_stream_id');
    }
}
