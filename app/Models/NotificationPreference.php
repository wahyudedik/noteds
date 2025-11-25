<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'notification_type',
        'in_app',
        'email',
        'push',
    ];

    protected function casts(): array
    {
        return [
            'in_app' => 'boolean',
            'email' => 'boolean',
            'push' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create preference for a user and notification type.
     */
    public static function getOrCreate(string $userId, string $notificationType): self
    {
        return static::firstOrCreate(
            [
                'user_id' => $userId,
                'notification_type' => $notificationType,
            ],
            [
                'in_app' => true,
                'email' => true,
                'push' => false,
            ]
        );
    }

    /**
     * Check if in-app notifications are enabled.
     */
    public function allowsInApp(): bool
    {
        return $this->in_app;
    }

    /**
     * Check if email notifications are enabled.
     */
    public function allowsEmail(): bool
    {
        return $this->email;
    }

    /**
     * Check if push notifications are enabled.
     */
    public function allowsPush(): bool
    {
        return $this->push;
    }
}
