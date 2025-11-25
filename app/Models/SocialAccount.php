<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'provider_token',
        'provider_refresh_token',
        'provider_data',
    ];

    protected function casts(): array
    {
        return [
            'provider_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Find social account by provider and provider ID.
     */
    public static function findByProvider(string $provider, string $providerId): ?self
    {
        return static::where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();
    }

    /**
     * Find or create social account.
     */
    public static function findOrCreate(string $userId, string $provider, string $providerId, array $data = []): self
    {
        return static::firstOrCreate(
            [
                'user_id' => $userId,
                'provider' => $provider,
            ],
            [
                'provider_id' => $providerId,
                'provider_token' => $data['token'] ?? null,
                'provider_refresh_token' => $data['refresh_token'] ?? null,
                'provider_data' => $data['data'] ?? null,
            ]
        );
    }
}
