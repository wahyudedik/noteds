<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEmailPreference extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'new_note_notifications',
        'weekly_digest',
        'abandoned_cart_emails',
        'marketing_emails',
        'sequence_emails',
    ];

    protected function casts(): array
    {
        return [
            'new_note_notifications' => 'boolean',
            'weekly_digest' => 'boolean',
            'abandoned_cart_emails' => 'boolean',
            'marketing_emails' => 'boolean',
            'sequence_emails' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

