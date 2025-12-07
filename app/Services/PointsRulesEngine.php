<?php

namespace App\Services;

use App\Models\PointsRule;
use App\Models\PointsActivity;
use App\Models\PointsAdminNotification;
use App\Models\PointsFraudFlag;
use App\Models\PointsRuleViolation;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PointsRulesEngine
{
    /**
     * Validate point earning activity
     */
    public function validateEarningActivity($user, $activity_data): array
    {
        $rules = PointsRule::getActiveEarningRules();
        $violations = [];
        $total_penalty = 0;

        foreach ($rules as $rule) {
            $violation = $rule->checkViolation($user, $activity_data);

            if ($violation) {
                $violations[] = $violation;
                $rule->recordViolation();
                $total_penalty += $rule->penalty_points ?? 0;
            }
        }

        // Check fraud patterns
        $fraud_check = $this->checkFraudPatterns($user, 'earning', $activity_data);
        if ($fraud_check) {
            $violations[] = $fraud_check;
        }

        return [
            'valid' => empty($violations),
            'violations' => $violations,
            'penalty_points' => $total_penalty,
            'requires_approval' => !empty($fraud_check) || !empty($violations),
        ];
    }

    /**
     * Validate point redemption activity
     */
    public function validateRedemptionActivity($user, $activity_data): array
    {
        $rules = PointsRule::getActiveRedemptionRules();
        $violations = [];
        $total_penalty = 0;

        foreach ($rules as $rule) {
            $violation = $rule->checkViolation($user, $activity_data);

            if ($violation) {
                $violations[] = $violation;
                $rule->recordViolation();
                $total_penalty += $rule->penalty_points ?? 0;
            }
        }

        // Check daily redemption limit
        $today_count = PointsActivity::where('user_id', $user->id)
            ->where('activity_type', 'redeemed')
            ->where('status', 'approved')
            ->whereDate('created_at', today())
            ->count();

        // Assume max 5 redemptions per day (can be configurable)
        if ($today_count >= 5) {
            $violations[] = [
                'rule' => 'daily_redemption_limit',
                'message' => 'Anda sudah mencapai batas penukaran poin harian (5x)',
            ];
        }

        // Check fraud patterns
        $fraud_check = $this->checkFraudPatterns($user, 'redemption', $activity_data);
        if ($fraud_check) {
            $violations[] = $fraud_check;
        }

        return [
            'valid' => empty($violations),
            'violations' => $violations,
            'penalty_points' => $total_penalty,
            'requires_approval' => !empty($fraud_check) || !empty($violations),
        ];
    }

    /**
     * Check for fraud patterns
     */
    public function checkFraudPatterns($user, $activity_type, $activity_data): ?array
    {
        $fraud_rules = PointsRule::getActiveFraudRules();
        $risk_score = 0;
        $fraud_reasons = [];

        // Check rapid redemptions
        $last_hour_count = PointsActivity::where('user_id', $user->id)
            ->where('activity_type', $activity_type)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($last_hour_count >= 3) {
            $risk_score += 30;
            $fraud_reasons[] = 'Aktivitas berulang cepat dalam 1 jam';
        }

        // Check last 24 hours
        $last_day_count = PointsActivity::where('user_id', $user->id)
            ->where('activity_type', $activity_type)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        if ($last_day_count >= 10) {
            $risk_score += 20;
            $fraud_reasons[] = 'Aktivitas berlebihan dalam 24 jam';
        }

        // Check impossible timing (multiple locations in short period)
        $last_activity = PointsActivity::where('user_id', $user->id)
            ->whereNotNull('ip_address')
            ->orderBy('created_at', 'desc')
            ->first();

        if (
            $last_activity &&
            $last_activity->ip_address !== ($activity_data['ip_address'] ?? null) &&
            $last_activity->created_at->diffInSeconds(now()) < 60
        ) {
            $risk_score += 40;
            $fraud_reasons[] = 'Lokasi IP berubah dalam waktu kurang dari 1 menit';
        }

        // Check if multiple discount redemptions on same transaction
        if (
            $activity_type === 'redeemed' &&
            isset($activity_data['related_id'])
        ) {
            $similar_redemptions = PointsActivity::where('related_id', $activity_data['related_id'])
                ->where('activity_type', 'redeemed')
                ->where('status', 'approved')
                ->count();

            if ($similar_redemptions >= 2) {
                $risk_score += 50;
                $fraud_reasons[] = 'Multiple redemption pada transaksi yang sama';
            }
        }

        if ($risk_score >= 50) {
            return [
                'rule' => 'fraud_detected',
                'risk_score' => $risk_score,
                'message' => 'Aktivitas mencurigakan terdeteksi: ' . implode(', ', $fraud_reasons),
                'fraud_reasons' => $fraud_reasons,
            ];
        }

        return null;
    }

    /**
     * Record points activity with validation
     */
    public function recordActivity(
        $user,
        $activity_type,
        $points_amount = 0,
        $source_type = null,
        $related_id = null,
        $additional_data = []
    ): PointsActivity {
        $activity_data = array_merge($additional_data, [
            'points_amount' => $points_amount,
            'source_type' => $source_type,
            'activity_type' => $activity_type,
        ]);

        // Determine if requires approval
        $requires_approval = false;
        $validation_result = [];

        if (in_array($activity_type, ['earned', 'redeemed'])) {
            if ($activity_type === 'earned') {
                $validation_result = $this->validateEarningActivity($user, $activity_data);
            } else {
                $validation_result = $this->validateRedemptionActivity($user, $activity_data);
            }
            $requires_approval = $validation_result['requires_approval'] ?? false;
        }

        // Create activity record
        $activity = PointsActivity::create([
            'user_id' => $user->id,
            'activity_type' => $activity_type,
            'points_amount' => $points_amount,
            'monetary_value' => $additional_data['monetary_value'] ?? null,
            'source_type' => $source_type,
            'related_id' => $related_id,
            'transaction_reference' => $additional_data['transaction_reference'] ?? null,
            'metadata' => $additional_data['metadata'] ?? null,
            'status' => $requires_approval ? 'pending' : 'approved',
            'is_suspicious' => $validation_result['fraud_check'] ?? false,
            'risk_score' => $validation_result['risk_score'] ?? 0,
            'ip_address' => $additional_data['ip_address'] ?? null,
            'user_agent' => $additional_data['user_agent'] ?? null,
            'approved_by' => !$requires_approval ? auth()->id() : null,
            'approved_at' => !$requires_approval ? now() : null,
        ]);

        // Handle violations
        if (!empty($validation_result['violations'])) {
            $this->handleViolations($user, $activity, $validation_result['violations']);
        }

        // Send notifications
        if ($requires_approval || $validation_result['fraud_check'] ?? false) {
            $this->notifyAdminOfActivity($user, $activity, $validation_result);
        }

        Log::info('Points activity recorded', [
            'user_id' => $user->id,
            'activity_type' => $activity_type,
            'points' => $points_amount,
            'requires_approval' => $requires_approval,
        ]);

        return $activity;
    }

    /**
     * Handle rule violations
     */
    private function handleViolations($user, $activity, $violations): void
    {
        foreach ($violations as $violation) {
            PointsRuleViolation::create([
                'user_id' => $user->id,
                'rule_id' => $violation['rule_id'] ?? null,
                'activity_id' => $activity->id,
                'violation_details' => $violation['message'] ?? 'Rule violation',
                'severity' => 1,
                'points_penalty' => $violation['penalty_points'] ?? 0,
                'status' => 'reported',
            ]);
        }
    }

    /**
     * Notify admin of activity
     */
    private function notifyAdminOfActivity($user, $activity, $validation_result): void
    {
        $admins = User::role('admin')->get();

        $notification_type = 'suspicious_activity';
        if (isset($validation_result['violations'])) {
            $notification_type = 'rule_violation';
        }

        foreach ($admins as $admin) {
            PointsAdminNotification::create([
                'admin_id' => $admin->id,
                'notification_type' => $notification_type,
                'message' => "User {$user->name} melakukan {$activity->activity_type} points (Risk: {$activity->risk_score}%)",
                'related_user_id' => $user->id,
                'related_activity_id' => $activity->id,
                'severity' => $activity->risk_score >= 50 ? 3 : 2,
                'action_url' => route('admin.points-activities.show', $activity->id),
            ]);
        }
    }

    /**
     * Create fraud flag
     */
    public function createFraudFlag($user, $flag_type, $description, $confidence_score = 50, $activity_id = null): void
    {
        PointsFraudFlag::create([
            'user_id' => $user->id,
            'flag_type' => $flag_type,
            'description' => $description,
            'severity' => $confidence_score >= 80 ? 3 : ($confidence_score >= 50 ? 2 : 1),
            'confidence_score' => $confidence_score,
            'triggered_by_activity_id' => $activity_id,
            'status' => 'pending',
            'auto_flagged' => true,
        ]);

        // Notify admins
        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            PointsAdminNotification::create([
                'admin_id' => $admin->id,
                'notification_type' => 'suspicious_activity',
                'message' => "Fraud flag untuk user {$user->name}: {$description}",
                'related_user_id' => $user->id,
                'severity' => $confidence_score >= 80 ? 3 : 2,
                'data' => ['confidence_score' => $confidence_score, 'flag_type' => $flag_type],
            ]);
        }
    }

    /**
     * Approve activity
     */
    public function approveActivity($activity_id, $admin_id, $notes = null): void
    {
        $activity = PointsActivity::findOrFail($activity_id);
        $activity->approve($admin_id, $notes);

        Log::info('Activity approved', [
            'activity_id' => $activity_id,
            'approved_by' => $admin_id,
        ]);
    }

    /**
     * Reject activity
     */
    public function rejectActivity($activity_id, $admin_id, $reason): void
    {
        $activity = PointsActivity::findOrFail($activity_id);

        // If it was a points earning, revert the points
        if ($activity->activity_type === 'earned' && $activity->points_amount > 0) {
            $activity->user->points -= $activity->points_amount;
            $activity->user->save();
        }

        $activity->reject($admin_id, $reason);

        Log::info('Activity rejected', [
            'activity_id' => $activity_id,
            'rejected_by' => $admin_id,
        ]);
    }
}
