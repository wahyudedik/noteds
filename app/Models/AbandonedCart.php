<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbandonedCart extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'note_id',
        'email',
        'ip_address',
        'viewed_at',
        'email_sent_at',
        'email_count',
        'purchased',
        'purchased_at',
    ];

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
            'email_sent_at' => 'datetime',
            'email_count' => 'integer',
            'purchased' => 'boolean',
            'purchased_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class, 'note_id');
    }
}

