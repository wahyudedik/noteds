# ⚡ POINTS SYSTEM - QUICK REFERENCE GUIDE

**Quick lookup for common tasks and issues**

---

## 🚀 Quick Start

### Access Points Rules Admin Panel
```
URL: http://noteds.test/admin/points-rules
Requires: Admin role with auth:sanctum
```

### Key Database Tables
```
✅ points_rules           → Rule definitions
✅ points_activities      → Activity audit trail
✅ points_admin_notifications → Admin alerts
✅ points_fraud_flags     → Fraud detection
✅ points_rule_violations → Rule violations
✅ points_system_config   → Configuration storage
```

---

## 📊 Activity Types & Statuses

### Activity Types
```
earned     → User earned points (purchase/referral/bonus)
redeemed   → User redeemed points for discount
used       → Points used in transaction
expired    → Points expired
refunded   → Points refunded (after order cancellation)
transferred → Points transferred to another user
deducted   → Admin deducted points (penalty)
adjusted   → Admin manually adjusted points
```

### Activity Status
```
pending    → Awaiting admin approval (high risk)
approved   → Activity completed successfully
flagged    → Marked as suspicious (> 70% risk)
rejected   → Activity rejected by admin
```

---

## 🎯 Default Rules (Pre-configured)

### Earning Rules
| # | Name | Rule | Penalty |
|---|------|------|---------|
| 1 | Purchase Earning | 1 poin per Rp 100 | N/A |
| 2 | Referral Bonus | 5.000 poin per successful referral | N/A |
| 3 | Sign-up Bonus | 1.000 poin on account creation | N/A |

### Redemption Rules
| # | Name | Rule | Penalty |
|---|------|------|---------|
| 4 | Daily Limit | Max 5 redemptions/day | N/A |
| 5 | Min Points Required | Check user has enough points | N/A |

### Marketplace Rules
| # | Name | Rule | Penalty |
|---|------|------|---------|
| 6 | Discount %Limit | Max 50% of transaction | N/A |
| 7 | No Multiple Discounts | 1 discount per transaction | -2.000 poin |

### Fraud Prevention
| # | Name | Rule | Penalty |
|---|------|------|---------|
| 8 | Rapid Redemptions | 3+ in 1 hour = suspicious | -1.000 poin |
| 9 | IP Change Detection | IP change < 60s = fraud | -5.000 poin |
| 10 | Account Takeover | New IP + unusual time | Block + Verify |

---

## ⚙️ Configuration Values

### Earning Config
```php
earning_rate              = 0.01          (1 point per Rp 100)
earning_description       = "Purchase"
referral_bonus            = 5000          (points per referral)
signup_bonus              = 1000          (points on signup)
```

### Redemption Config
```php
daily_redemption_limit    = 5             (per day)
hourly_redemption_limit   = 3             (per hour, optional)
min_points_to_redeem      = 1000          (minimum)
```

### Marketplace Config
```php
max_discount_percent      = 50            (max 50% discount)
max_discount_amount       = 500000        (Rp 500K max)
points_to_rupiah_rate     = 10            (1 point = Rp 10)
```

### Fraud Config
```php
fraud_ip_threshold        = 60            (seconds)
fraud_confidence_high     = 80            (0-100 scale)
rapid_redemption_count    = 3             (per hour)
high_volume_threshold     = 10            (per 24h)
auto_suspend_on_high_fraud = true         (boolean)
suspension_days           = 7             (days)
```

---

## 🔍 Common Admin Tasks

### Check Unread Notifications
```php
$notifications = PointsAdminNotification::getUnreadForAdmin(
    admin_id: auth()->id(),
    limit: 20
);

foreach ($notifications as $notif) {
    echo $notif->title . " - Severity: " . $notif->severity;
}
```

### Review Pending Activities
```php
$pending = PointsActivity::getPending(limit: 50);

foreach ($pending as $activity) {
    // Review risk_score and fraud_flag_reason
    // Decide: approve() or reject()
}
```

### Investigate Fraud Flag
```php
$fraud = PointsFraudFlag::find($fraud_id);

// Investigate and take action
$fraud->investigate(
    admin_id: auth()->id(),
    notes: "IP verified as legitimate VPN",
    action: 'false_positive' // or 'suspend', 'monitor'
);
```

### Handle Rule Violation
```php
$violation = PointsRuleViolation::find($violation_id);

// Review and decide
if ($user_is_innocent) {
    $violation->approveAppeal(
        admin_id: auth()->id(),
        notes: "Appeal approved - user was legitimate"
    );
}
```

---

## 🚨 Risk Scoring

### Risk Calculation

```
Base: 0 points

Add points for:
+30  → Rapid redemptions (3+ in 1 hour)
+40  → Impossible timing (IP < 60s change)
+20  → High volume (10+ in 24h)
+50  → Duplicate discount on same transaction
+40  → Multiple same-day referrals
+30  → Device change in 24h

Thresholds:
 0-29  → LOW risk → Auto-approved
30-70  → MEDIUM risk → Pending admin approval
71-100 → HIGH risk → Auto-flagged for investigation
```

---

## 📋 Admin Notification Types

| Type | Severity | When Triggered | Action |
|------|----------|---|--------|
| discount_used | 1 (Low) | User applies point discount | Monitor |
| redemption_completed | 1 (Low) | Redemption finished | Monitor |
| daily_limit_reached | 2 (Med) | User hits daily limit | Notify user |
| suspicious_activity | 3 (High) | Risk > 70% | Investigate |
| rule_violation | 2 (Med) | Rule broken | Review |
| user_limit_warning | 2 (Med) | Approaching limits | Warn user |
| high_value_redemption | 3 (High) | Large redemption | Review |

---

## 🔧 Common Code Snippets

### Award Points Manually
```php
use App\Services\PointsRulesEngine;

$engine = app(PointsRulesEngine::class);
$user = User::find($user_id);

$activity = $engine->recordActivity(
    $user,
    'earned',
    5000,
    'approved',
    ['reason' => 'Manual adjustment by admin']
);

$user->increment('points', 5000);
```

### Deduct Points (Penalty)
```php
$activity = $engine->recordActivity(
    $user,
    'deducted',
    1000,
    'approved',
    ['reason' => 'Rule violation penalty']
);

$user->decrement('points', 1000);
```

### Apply Discount to Order
```php
$engine = app(PointsRulesEngine::class);

$result = $engine->validateRedemptionActivity(
    $user,
    5000,
    ['order_id' => $order->id]
);

if ($result['valid']) {
    $discount = 50000; // 5000 points * 10
    $order->discount_from_points = $discount;
    $order->final_price = $order->subtotal - $discount;
    $order->save();
    
    $user->decrement('points', 5000);
}
```

### Create Fraud Flag Manually
```php
PointsFraudFlag::create([
    'user_id' => $user_id,
    'activity_id' => $activity_id,
    'flag_type' => 'rapid_redemptions',
    'confidence' => 85,
    'evidence' => ['redemptions' => ['14:00', '14:15', '14:30']],
    'status' => 'pending',
    'severity' => 3
]);
```

### Create Rule Violation
```php
PointsRuleViolation::create([
    'user_id' => $user_id,
    'rule_id' => $rule_id,
    'violation_details' => ['attempted_value' => 100000],
    'severity' => 2,
    'points_penalty' => 2000,
    'status' => 'reported'
]);
```

---

## 🧪 Quick Testing

### Test Rapid Redemption Detection
```bash
# In tinker or test
for ($i = 0; $i < 3; $i++) {
    app(PointsRulesEngine::class)->recordActivity(
        auth()->user(),
        'redeemed',
        1000,
        'approved'
    );
}

$result = app(PointsRulesEngine::class)->validateRedemptionActivity(
    auth()->user(),
    1000
);

echo "Risk score: " . $result['risk_score']; // Should be > 30
```

### Test Fraud Detection
```php
app(PointsRulesEngine::class)->checkFraudPatterns(
    $user,
    'redeemed',
    [
        'ip_address' => '103.27.1.50',
        'last_ip' => '192.168.1.100',
        'time_since_last' => 45 // seconds
    ]
);
// Should detect impossible timing (< 60s IP change)
```

---

## 🐛 Debugging Tips

### Check User's Point History
```php
$user = User::find($user_id);
$activities = PointsActivity::where('user_id', $user->id)
    ->orderBy('created_at', 'desc')
    ->limit(20)
    ->get();

foreach ($activities as $a) {
    echo "{$a->activity_type}: {$a->points_amount} ({$a->status})\n";
}
```

### Check Fraud Flags for User
```php
$flags = PointsFraudFlag::where('user_id', $user_id)
    ->where('status', '!=', 'resolved')
    ->get();

foreach ($flags as $flag) {
    echo "{$flag->flag_type} - Confidence: {$flag->confidence}%\n";
}
```

### Check Pending Admin Actions
```php
$pending_activities = PointsActivity::where('status', 'pending')->count();
$pending_violations = PointsRuleViolation::where('status', 'reported')->count();
$pending_fraud = PointsFraudFlag::where('status', 'pending')->count();

echo "Pending: {$pending_activities} activities, {$pending_violations} violations, {$pending_fraud} fraud flags";
```

---

## 📞 Emergency Actions

### Suspend User Points (Fraud Suspected)
```php
$fraud_flag = PointsFraudFlag::find($fraud_id);
$fraud_flag->suspend(days: 30);

// This prevents any redemption until suspension_until date
```

### Reverse Recent Transactions
```php
$activities = PointsActivity::where('user_id', $user_id)
    ->where('created_at', '>=', now()->subHours(1))
    ->get();

foreach ($activities as $activity) {
    $activity->reject(auth()->id(), 'Emergency reversal');
    $user->increment('points', $activity->points_amount);
}
```

### Disable a Rule Immediately
```php
PointsRule::find($rule_id)->update(['is_active' => false]);
// Rule will no longer be checked during validation
```

---

## 📚 File Locations

```
Models:
- app/Models/PointsRule.php
- app/Models/PointsActivity.php
- app/Models/PointsAdminNotification.php
- app/Models/PointsFraudFlag.php
- app/Models/PointsRuleViolation.php
- app/Models/PointsSystemConfig.php

Service:
- app/Services/PointsRulesEngine.php

Controller:
- app/Http/Controllers/Admin/PointsRulesManagementController.php

Migrations:
- database/migrations/2025_12_07_create_points_rules_table.php
- database/migrations/2025_12_07_create_points_system_config_table.php

Seeder:
- database/seeders/PointsRulesSeeder.php

Documentation:
- POINTS_SYSTEM_RULES.md (Admin/Business Guide)
- POINTS_SYSTEM_TECHNICAL.md (Developer Guide)
- POINTS_SYSTEM_QUICK_REFERENCE.md (This file)
```

---

## ✅ Status & Support

**Status:** ✅ Production Ready  
**Last Updated:** December 7, 2025  
**Framework:** Laravel 12  
**Database:** MySQL with UUIDs

**Documentation:**
- 📋 **POINTS_SYSTEM_RULES.md** - For admin/business users
- 🔧 **POINTS_SYSTEM_TECHNICAL.md** - For developers
- ⚡ **POINTS_SYSTEM_QUICK_REFERENCE.md** - For quick lookups (this file)

---

**Version:** 2.0
