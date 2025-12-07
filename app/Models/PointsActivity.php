<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointsActivity extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'points_activities';

    protected $fillable = [
        'user_id',
        'activity_type',
        'points_amount',
        'monetary_value',
        'source_type',
        'related_id',
        'transaction_reference',
        'metadata',
        'status',
        'rule_id',
        'is_suspicious',
        'fraud_flag_reason',
        'risk_score',
        'ip_address',
        'user_agent',
        'approved_by',
        'approved_at',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'points_amount' => 'integer',
            'monetary_value' => 'decimal:2',
            'metadata' => 'array',
            'is_suspicious' => 'boolean',
            'risk_score' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * Relation: User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation: Rule (if triggered by a rule)
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(PointsRule::class, 'rule_id');
    }

    /**
     * Get the user who approved this activity
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Approve this activity
     */
    public function approve($admin_id, $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $admin_id,
            'approved_at' => now(),
            'admin_notes' => $notes,
        ]);
    }

    /**
     * Reject this activity
     */
    public function reject($admin_id, $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $admin_id,
            'approved_at' => now(),
            'admin_notes' => $reason,
        ]);
    }

    /**
     * Flag as suspicious
     */
    public function flagAsSuspicious($reason, $risk_score = 0): void
    {
        $this->update([
            'is_suspicious' => true,
            'fraud_flag_reason' => $reason,
            'risk_score' => $risk_score,
            'status' => 'flagged',
        ]);
    }

    /**
     * Get activities by user and type
     */
    public static function getByUserAndType($user_id, $type, $days = 30)
    {
        return static::where('user_id', $user_id)
            ->where('activity_type', $type)
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get suspicious activities
     */
    public static function getSuspicious($limit = 50)
    {
        return static::where('is_suspicious', true)
            ->where('status', '!=', 'rejected')
            ->orderBy('risk_score', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get pending approvals
     */
    public static function getPending($limit = 50)
    {
        return static::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Calculate total points in period
     */
    public static function getTotalByUserInPeriod($user_id, $activity_type, $start, $end)
    {
        return static::where('user_id', $user_id)
            ->where('activity_type', $activity_type)
            ->where('status', 'approved')
            ->whereBetween('created_at', [$start, $end])
            ->sum('points_amount');
    }
}
