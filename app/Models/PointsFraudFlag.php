<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PointsFraudFlag extends Model
{
    use HasUuids;

    protected $table = 'points_fraud_flags';

    protected $fillable = [
        'user_id',
        'flag_type',
        'description',
        'severity',
        'confidence_score',
        'evidence',
        'triggered_by_activity_id',
        'status',
        'investigated_by',
        'investigated_at',
        'investigation_notes',
        'auto_flagged',
        'points_suspended',
        'suspension_until',
    ];

    protected function casts(): array
    {
        return [
            'evidence' => 'array',
            'severity' => 'integer',
            'confidence_score' => 'integer',
            'auto_flagged' => 'boolean',
            'points_suspended' => 'boolean',
            'suspension_until' => 'datetime',
            'investigated_at' => 'datetime',
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
     * Investigate this flag
     */
    public function investigate($admin_id, $notes, $action): void
    {
        $this->update([
            'status' => 'investigating',
            'investigated_by' => $admin_id,
            'investigated_at' => now(),
            'investigation_notes' => $notes,
        ]);

        if ($action === 'suspend') {
            $this->suspend();
        } elseif ($action === 'false_positive') {
            $this->update(['status' => 'false_positive']);
        }
    }

    /**
     * Suspend user points
     */
    public function suspend($days = 7): void
    {
        $this->update([
            'points_suspended' => true,
            'suspension_until' => now()->addDays($days),
            'status' => 'resolved',
        ]);
    }
}
