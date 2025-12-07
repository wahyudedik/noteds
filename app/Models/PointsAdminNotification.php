<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointsAdminNotification extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'points_admin_notifications';

    protected $fillable = [
        'admin_id',
        'notification_type',
        'message',
        'related_user_id',
        'related_activity_id',
        'data',
        'is_read',
        'read_at',
        'is_actioned',
        'actioned_at',
        'severity',
        'action_url',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'is_actioned' => 'boolean',
            'actioned_at' => 'datetime',
            'severity' => 'integer',
        ];
    }

    /**
     * Relation: Admin user
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Relation: Related user
     */
    public function relatedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }

    /**
     * Relation: Related activity
     */
    public function relatedActivity(): BelongsTo
    {
        return $this->belongsTo(PointsActivity::class, 'related_activity_id');
    }

    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark as actioned
     */
    public function markAsActioned(): void
    {
        $this->update([
            'is_actioned' => true,
            'actioned_at' => now(),
        ]);
    }

    /**
     * Get unread notifications for admin
     */
    public static function getUnreadForAdmin($admin_id, $limit = 20)
    {
        return static::where('admin_id', $admin_id)
            ->where('is_read', false)
            ->orderBy('severity', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get pending action notifications
     */
    public static function getPendingAction($admin_id = null, $limit = 20)
    {
        $query = static::where('is_actioned', false);

        if ($admin_id) {
            $query->where('admin_id', $admin_id);
        }

        return $query->orderBy('severity', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Count unread notifications
     */
    public static function countUnreadForAdmin($admin_id): int
    {
        return static::where('admin_id', $admin_id)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get high severity notifications
     */
    public static function getHighSeverity($limit = 10)
    {
        return static::where('severity', '>=', 3)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
