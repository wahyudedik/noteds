<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EmailUnsubscribe extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'email',
        'token',
        'reason',
        'feedback',
        'unsubscribed_at',
    ];

    protected function casts(): array
    {
        return [
            'unsubscribed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Generate unique unsubscribe token
     */
    public static function generateToken(): string
    {
        return Str::random(64);
    }

    /**
     * Check if email is unsubscribed
     */
    public static function isUnsubscribed(string $email): bool
    {
        return self::where('email', $email)->exists();
    }

    /**
     * Unsubscribe email
     */
    public static function unsubscribe(string $email, ?string $reason = null, ?string $feedback = null, ?string $userId = null): self
    {
        return self::create([
            'user_id' => $userId,
            'email' => $email,
            'token' => self::generateToken(),
            'reason' => $reason,
            'feedback' => $feedback,
            'unsubscribed_at' => now(),
        ]);
    }
}

