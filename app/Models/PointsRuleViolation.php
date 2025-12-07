<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PointsRuleViolation extends Model
{
    use HasUuids;

    protected $table = 'points_rule_violations';

    protected $fillable = [
        'user_id',
        'rule_id',
        'activity_id',
        'violation_details',
        'severity',
        'points_penalty',
        'status',
        'reviewed_by',
        'reviewed_at',
        'admin_decision',
        'user_appeal',
        'appeal_approved',
    ];

    protected function casts(): array
    {
        return [
            'severity' => 'integer',
            'points_penalty' => 'integer',
            'reviewed_at' => 'datetime',
            'appeal_approved' => 'boolean',
        ];
    }

    /**
     * Get user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get rule
     */
    public function rule()
    {
        return $this->belongsTo(PointsRule::class);
    }

    /**
     * Appeal this violation
     */
    public function appeal($reason): void
    {
        $this->update([
            'status' => 'appealed',
            'user_appeal' => $reason,
        ]);
    }

    /**
     * Approve appeal
     */
    public function approveAppeal($admin_id, $notes): void
    {
        $this->update([
            'appeal_approved' => true,
            'reviewed_by' => $admin_id,
            'reviewed_at' => now(),
            'admin_decision' => $notes,
            'status' => 'acknowledged',
        ]);
    }
}
