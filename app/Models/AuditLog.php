<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AuditLog Model
 * Stores security audit trails untuk semua sensitive operations
 */
class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'data',
    ];

    protected $casts = [
        'data' => 'json',
        'created_at' => 'datetime',
    ];

    protected $hidden = [
        'updated_at',
    ];

    /**
     * User who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get IP address from data
     */
    public function getIpAddressAttribute(): ?string
    {
        return $this->data['ip_address'] ?? null;
    }

    /**
     * Scope: Filter by action
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: Filter by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Recent logs
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: Get suspicious activities
     */
    public function scopeSuspicious($query)
    {
        return $query->where('action', 'suspicious_activity');
    }

    /**
     * Check if this is a sensitive action
     */
    public function isSensitiveAction(): bool
    {
        $sensitiveActions = [
            'password_change',
            'email_change',
            'permission_change',
            'user_suspension',
            'refund',
            'admin_action',
            'suspicious_activity',
        ];

        return in_array($this->action, $sensitiveActions);
    }

    /**
     * Get readable timestamp
     */
    public function getTimestampAttribute(): string
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }
}
