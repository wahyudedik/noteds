<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * API Token Model
 * 
 * Manages secure API tokens for programmatic access to the application.
 * Each token is:
 * - SHA256 hashed for security
 * - Scoped to specific endpoints
 * - Can be revoked immediately
 * - Includes expiration dates
 * - Tracks last usage for monitoring
 */
class ApiToken extends Model
{
    use HasFactory;

    protected $table = 'api_tokens';

    protected $fillable = [
        'user_id',
        'name',
        'token',
        'scopes',
        'last_used_at',
        'expires_at',
        'revoked',
    ];

    protected $casts = [
        'scopes' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'token', // Never expose token in API responses
    ];

    /**
     * Relationship: API token belongs to a user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if token has specific scope
     */
    public function hasScope(string $scope): bool
    {
        return in_array($scope, $this->scopes ?? []);
    }

    /**
     * Check if token has all required scopes
     */
    public function hasAllScopes(array $requiredScopes): bool
    {
        $tokenScopes = $this->scopes ?? [];

        foreach ($requiredScopes as $scope) {
            if (!in_array($scope, $tokenScopes)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if token is active (not revoked and not expired)
     */
    public function isActive(): bool
    {
        if ($this->revoked) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Revoke token immediately
     */
    public function revoke(): void
    {
        $this->update(['revoked' => true]);
    }

    /**
     * Extend token expiration
     */
    public function extendExpiration(\DateTimeInterface $newExpiration): void
    {
        $this->update(['expires_at' => $newExpiration]);
    }

    /**
     * Scope: Get only active tokens
     */
    public function scopeActive($query)
    {
        return $query->where('revoked', false)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope: Get recently used tokens
     */
    public function scopeRecentlyUsed($query, int $days = 7)
    {
        return $query->where('last_used_at', '>', now()->subDays($days));
    }

    /**
     * Scope: Get tokens without recent usage
     */
    public function scopeUnused($query, int $days = 30)
    {
        return $query->where('last_used_at', '<', now()->subDays($days))
            ->orWhereNull('last_used_at');
    }
}
