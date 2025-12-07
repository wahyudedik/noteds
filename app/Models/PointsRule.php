<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointsRule extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'points_rules';

    protected $fillable = [
        'category',
        'name',
        'description',
        'conditions',
        'priority',
        'is_active',
        'max_attempts',
        'cooldown_minutes',
        'penalty_points',
        'notify_admin',
        'notify_user',
        'notification_type',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'priority' => 'integer',
            'is_active' => 'boolean',
            'max_attempts' => 'integer',
            'cooldown_minutes' => 'integer',
            'penalty_points' => 'decimal:2',
            'notify_admin' => 'boolean',
            'notify_user' => 'boolean',
            'violation_count' => 'integer',
            'last_violation_at' => 'datetime',
        ];
    }

    /**
     * Get all violations of this rule
     */
    public function violations(): HasMany
    {
        return $this->hasMany(PointsRuleViolation::class, 'rule_id');
    }

    /**
     * Get all activities that triggered this rule
     */
    public function activities(): HasMany
    {
        return $this->hasMany(PointsActivity::class, 'rule_id');
    }

    /**
     * Get active earning rules
     */
    public static function getActiveEarningRules()
    {
        return static::where('category', 'earning')
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * Get active redemption rules
     */
    public static function getActiveRedemptionRules()
    {
        return static::where('category', 'redemption')
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * Get active fraud prevention rules
     */
    public static function getActiveFraudRules()
    {
        return static::where('category', 'fraud_prevention')
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * Check if a user violates this rule
     */
    public function checkViolation($user, $activity_data): ?array
    {
        if (!$this->is_active) {
            return null;
        }

        // Evaluate conditions
        $conditions = $this->conditions ?? [];

        foreach ($conditions as $condition) {
            $result = $this->evaluateCondition($condition, $user, $activity_data);
            if (!$result) {
                return null; // Rule not violated
            }
        }

        return [
            'rule_id' => $this->id,
            'violated' => true,
            'penalty_points' => $this->penalty_points,
            'message' => "Aturan '{$this->name}' dilanggar: {$this->description}",
        ];
    }

    /**
     * Evaluate a single condition
     */
    private function evaluateCondition($condition, $user, $activity_data): bool
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? '=';
        $value = $condition['value'] ?? null;

        if (!$field) {
            return true;
        }

        $actual_value = $activity_data[$field] ?? null;

        return match ($operator) {
            '=' => $actual_value == $value,
            '!=' => $actual_value != $value,
            '>' => $actual_value > $value,
            '<' => $actual_value < $value,
            '>=' => $actual_value >= $value,
            '<=' => $actual_value <= $value,
            'in' => in_array($actual_value, (array)$value),
            'not_in' => !in_array($actual_value, (array)$value),
            'contains' => strpos((string)$actual_value, (string)$value) !== false,
            default => true,
        };
    }

    /**
     * Increment violation count
     */
    public function recordViolation(): void
    {
        $this->increment('violation_count');
        $this->update(['last_violation_at' => now()]);
    }
}
