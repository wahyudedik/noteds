# 🔧 POINTS SYSTEM - TECHNICAL IMPLEMENTATION GUIDE

**For Developers: Integration, Testing, and Troubleshooting**

**Version:** 2.0  
**Last Updated:** December 7, 2025  
**Framework:** Laravel 12

---

## 📋 Table of Contents

1. [Architecture Overview](#architecture)
2. [Database Schema](#database)
3. [Core Components](#components)
4. [API Endpoints](#endpoints)
5. [Integration Examples](#integration)
6. [Testing Guide](#testing)
7. [Troubleshooting](#troubleshooting)

---

## <a name="architecture"></a>1. ARCHITECTURE OVERVIEW

### System Components

```
┌─────────────────────────────────────────────────────────┐
│                   POINTS SYSTEM v2.0                    │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ┌──────────────────────────────────────────────────┐   │
│  │     User Actions (Buy, Redeem, Earn, etc)       │   │
│  └────────────────────┬─────────────────────────────┘   │
│                       │                                  │
│                       ↓                                  │
│  ┌──────────────────────────────────────────────────┐   │
│  │    PointsRulesEngine (Validation & Scoring)      │   │
│  │  • validateEarningActivity()                     │   │
│  │  • validateRedemptionActivity()                  │   │
│  │  • checkFraudPatterns()                          │   │
│  │  • recordActivity()                              │   │
│  │  • createFraudFlag()                             │   │
│  └────────────────────┬─────────────────────────────┘   │
│                       │                                  │
│      ┌────────────────┼────────────────┐                │
│      ↓                ↓                ↓                │
│  ┌────────┐  ┌──────────────┐  ┌──────────────┐        │
│  │ RULES  │  │  ACTIVITIES  │  │ FRAUD FLAGS  │        │
│  └────────┘  └──────────────┘  └──────────────┘        │
│      ↓                ↓                ↓                │
│  ┌──────────────────────────────────────────────────┐   │
│  │        Database (MySQL with UUIDs)               │   │
│  │  • points_rules                                  │   │
│  │  • points_activities                             │   │
│  │  • points_admin_notifications                    │   │
│  │  • points_fraud_flags                            │   │
│  │  • points_rule_violations                        │   │
│  │  • points_system_config                          │   │
│  └──────────────────────────────────────────────────┘   │
│                       │                                  │
│                       ↓                                  │
│  ┌──────────────────────────────────────────────────┐   │
│  │  Admin Panel (PointsRulesManagementController)   │   │
│  │  • Manage Rules                                  │   │
│  │  • Review Violations                             │   │
│  │  • Investigate Fraud                             │   │
│  │  • Approve Activities                            │   │
│  │  • Monitor Notifications                         │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### Request Flow Diagram

```
Marketplace Purchase
    ↓
[1] Check Point Discount Eligibility
    ├─ Points balance sufficient?
    ├─ Daily limit not exceeded?
    ├─ No fraud flags?
    └─ Valid redemption rules?
    ↓
[2] PointsRulesEngine::validateRedemptionActivity()
    ├─ Check all applicable rules
    ├─ Calculate fraud risk score
    └─ Determine if needs approval
    ↓
[3a] Risk < 30%? → APPROVED
     ├─ Apply discount immediately
     ├─ Record activity as 'approved'
     ├─ Deduct points from user
     └─ Send notification
    ↓
[3b] Risk 30-70%? → PENDING
     ├─ Hold discount
     ├─ Record activity as 'pending'
     ├─ Notify admin for approval
     └─ User can't see discount yet
    ↓
[3c] Risk > 70%? → FLAGGED
     ├─ Reject discount
     ├─ Create fraud flag
     ├─ Notify admin + user
     └─ Require verification
```

---

## <a name="database"></a>2. DATABASE SCHEMA

### Table Structure

#### points_rules
```sql
CREATE TABLE points_rules (
  id CHAR(36) PRIMARY KEY,
  category ENUM('earning', 'redemption', 'usage', 'marketplace', 'fraud_prevention'),
  name VARCHAR(255),
  description TEXT,
  conditions JSON,
  priority INT DEFAULT 1000,
  penalty_points INT DEFAULT 0,
  is_active BOOLEAN DEFAULT true,
  violation_count INT DEFAULT 0,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

-- Example Rule:
{
  "id": "abc123",
  "category": "fraud_prevention",
  "name": "Rapid Redemption Detection",
  "conditions": {
    "operator": "AND",
    "rules": [
      {
        "field": "redemption_count_1h",
        "operator": ">=",
        "value": 3
      }
    ]
  }
}
```

#### points_activities
```sql
CREATE TABLE points_activities (
  id CHAR(36) PRIMARY KEY,
  user_id CHAR(36) FOREIGN KEY,
  activity_type ENUM('earned', 'redeemed', 'used', 'expired', 'refunded'),
  points_amount INT,
  status ENUM('pending', 'approved', 'flagged', 'rejected'),
  risk_score INT (0-100),
  ip_address VARCHAR(45),
  user_agent TEXT,
  fraud_flag_reason VARCHAR(255),
  description TEXT,
  metadata JSON,
  created_at TIMESTAMP
);

-- Example Activity:
{
  "id": "activity123",
  "user_id": "user456",
  "activity_type": "redeemed",
  "points_amount": 5000,
  "status": "approved",
  "risk_score": 15,
  "ip_address": "192.168.1.100",
  "fraud_flag_reason": null,
  "metadata": {
    "order_id": "order789",
    "discount_amount": 50000,
    "original_price": 500000
  }
}
```

#### points_admin_notifications
```sql
CREATE TABLE points_admin_notifications (
  id CHAR(36) PRIMARY KEY,
  user_id CHAR(36),
  activity_id CHAR(36),
  notification_type VARCHAR(255),
  severity INT (1-3),
  title VARCHAR(255),
  message TEXT,
  data JSON,
  action_url VARCHAR(255),
  is_read BOOLEAN DEFAULT false,
  is_actioned BOOLEAN DEFAULT false,
  actioned_by CHAR(36),
  actioned_at TIMESTAMP,
  created_at TIMESTAMP
);
```

#### points_fraud_flags
```sql
CREATE TABLE points_fraud_flags (
  id CHAR(36) PRIMARY KEY,
  user_id CHAR(36),
  activity_id CHAR(36),
  flag_type VARCHAR(255),
  confidence INT (0-100),
  evidence JSON,
  status ENUM('pending', 'investigating', 'resolved', 'false_positive'),
  severity INT (1-3),
  created_at TIMESTAMP
);
```

#### points_rule_violations
```sql
CREATE TABLE points_rule_violations (
  id CHAR(36) PRIMARY KEY,
  user_id CHAR(36),
  rule_id CHAR(36),
  violation_details JSON,
  severity INT (1-3),
  points_penalty INT,
  status ENUM('reported', 'acknowledged', 'warned', 'penalized', 'appealed'),
  user_appeal TEXT,
  appeal_approved BOOLEAN,
  admin_decision TEXT,
  created_at TIMESTAMP
);
```

#### points_system_config
```sql
CREATE TABLE points_system_config (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  key VARCHAR(255) UNIQUE,
  value TEXT,
  type ENUM('string', 'integer', 'decimal', 'boolean', 'json'),
  category VARCHAR(100),
  description TEXT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

-- Example Config Values:
earning_rate: 0.01 (type: decimal)
referral_bonus: 5000 (type: integer)
daily_redemption_limit: 5 (type: integer)
max_discount_percent: 50 (type: integer)
fraud_ip_threshold: 60 (type: integer, seconds)
```

---

## <a name="components"></a>3. CORE COMPONENTS

### A. PointsRulesEngine Service

**Location:** `app/Services/PointsRulesEngine.php`

**Key Methods:**

#### 1. validateEarningActivity()
```php
public function validateEarningActivity($user, $activity_data)
  
Returns:
[
  'valid' => true|false,
  'violations' => [],
  'penalty_points' => 0,
  'requires_approval' => false,
  'risk_score' => 0-100
]
```

**Usage:**
```php
$engine = new PointsRulesEngine();
$result = $engine->validateEarningActivity(
  auth()->user(),
  [
    'activity_type' => 'purchase',
    'amount' => 500000,
    'referrer_id' => null
  ]
);

if ($result['valid']) {
  // Award points
  user()->points += 5000; // 1% of 500000
} else {
  // Log violation
}
```

#### 2. validateRedemptionActivity()
```php
public function validateRedemptionActivity($user, $points_to_redeem, $context = [])

Context parameters:
- order_id: string
- discount_percent: int
- transaction_amount: int
```

**Usage:**
```php
$result = $engine->validateRedemptionActivity(
  auth()->user(),
  5000, // points to redeem
  [
    'order_id' => 'ORDER-123',
    'discount_percent' => 10,
    'transaction_amount' => 500000
  ]
);

if ($result['valid']) {
  // Apply discount
  $discount = 50000; // 10% of 500000
  $order->discount_from_points = $discount;
} else {
  // Reject discount
  abort(403, "Cannot redeem points: " . $result['violations'][0]);
}
```

#### 3. checkFraudPatterns()
```php
public function checkFraudPatterns($user, $activity_type, $data)

Returns:
{
  'is_fraud': boolean,
  'confidence': 0-100,
  'flags': [],
  'risk_score': 0-100
}
```

**Fraud Detection Patterns:**

```php
// Pattern 1: Rapid Redemptions
if (recentRedemptions >= 3 AND timespan == '1 hour') {
  $risk_score += 30;
  $patterns[] = 'rapid_redemptions';
}

// Pattern 2: Impossible Timing
if (timeSinceLastIpChange < 60 seconds) {
  $risk_score += 40;
  $patterns[] = 'impossible_timing';
}

// Pattern 3: High Volume
if (redeemsInLast24h > 10) {
  $risk_score += 20;
  $patterns[] = 'high_volume';
}

// Pattern 4: Duplicate Discount
if (multipleDiscountsOnSameOrder) {
  $risk_score += 50;
  $patterns[] = 'duplicate_discount';
}
```

#### 4. recordActivity()
```php
public function recordActivity(
  $user,
  $activity_type,
  $points_amount,
  $status = 'pending',
  $metadata = []
)

Returns: PointsActivity model instance
```

**Usage:**
```php
$activity = $engine->recordActivity(
  $user,
  'redeemed',
  5000,
  'approved',
  [
    'order_id' => 'ORD-123',
    'discount_amount' => 50000
  ]
);

// Activity is now in database and admin is notified
```

#### 5. notifyAdminOfActivity()
```php
public function notifyAdminOfActivity($activity, $notification_type, $severity = 1)

Notification types:
- discount_used (when points used for purchase)
- redemption_completed (when redemption done)
- suspicious_activity (when risk > threshold)
- rule_violation (when rule broken)
- daily_limit_reached (when user hits daily limit)
```

---

### B. Models

#### PointsRule Model
```php
// Get active earning rules
$earning_rules = PointsRule::getActiveEarningRules();

// Get active redemption rules
$redemption_rules = PointsRule::getActiveRedemptionRules();

// Get active fraud rules
$fraud_rules = PointsRule::getActiveFraudRules();

// Check if rule violated
$violated = $rule->checkViolation(
  $user,
  ['redemption_count_1h' => 4]
);
```

#### PointsActivity Model
```php
// Get suspicious activities
$suspicious = PointsActivity::getSuspicious(limit: 20);

// Get pending approval
$pending = PointsActivity::getPending(limit: 10);

// Get by user
$user_activities = PointsActivity::getByUserAndType(
  user_id: 'user-123',
  type: 'redeemed',
  days: 30
);

// Approve activity
$activity->approve(
  admin_id: auth()->id(),
  notes: 'Verified legitimate purchase'
);

// Reject activity
$activity->reject(
  admin_id: auth()->id(),
  reason: 'Suspicious pattern detected'
);
```

#### PointsAdminNotification Model
```php
// Get unread notifications
$unread = PointsAdminNotification::getUnreadForAdmin(
  admin_id: 'admin-456',
  limit: 20
);

// Get high severity
$critical = PointsAdminNotification::getHighSeverity(limit: 10);

// Mark as read
$notification->markAsRead();

// Mark as actioned
$notification->markAsActioned();
```

#### PointsFraudFlag Model
```php
// Get pending investigations
$pending = PointsFraudFlag::where('status', 'pending')->get();

// Investigate fraud
$fraud_flag->investigate(
  admin_id: auth()->id(),
  notes: 'IP address verified as VPN',
  action: 'false_positive'
);

// Suspend user
$fraud_flag->suspend(days: 7);
```

---

### C. Controller Methods

**Location:** `app/Http/Controllers/Admin/PointsRulesManagementController.php`

#### 1. Manage Rules
```php
// List rules
GET /admin/points-rules
Query params: ?category=earning&status=active

// Create new rule
GET /admin/points-rules/create
POST /admin/points-rules

// Edit rule
GET /admin/points-rules/{id}/edit
PUT /admin/points-rules/{id}

// Delete rule
DELETE /admin/points-rules/{id}
```

#### 2. Review Violations
```php
// List violations
GET /admin/points-rules/violations
Query params: ?severity=3&status=pending

// Review single violation
GET /admin/points-rules/violations/{id}
PUT /admin/points-rules/violations/{id}/review
Data: {
  action: 'warn|penalize|reject',
  notes: 'explanation'
}
```

#### 3. Investigate Fraud
```php
// List fraud flags
GET /admin/points-rules/fraud-flags
Query params: ?severity=3&status=pending

// Investigate fraud
PUT /admin/points-rules/fraud-flags/{id}/investigate
Data: {
  action: 'false_positive|suspend|monitor',
  notes: 'investigation notes'
}
```

#### 4. Review Activities
```php
// List pending activities
GET /admin/points-rules/pending-activities

// Approve activity
PUT /admin/points-rules/activities/{id}/approve
Data: { notes: 'approved' }

// Reject activity
PUT /admin/points-rules/activities/{id}/reject
Data: { reason: 'fraud detected' }
```

#### 5. Admin Notifications
```php
// List notifications
GET /admin/points-rules/notifications
Query params: ?severity=2&unread=true

// Mark as read
PUT /admin/points-rules/notifications/{id}/read

// Take action
PUT /admin/points-rules/notifications/{id}/action
Data: { action: 'approved|rejected' }
```

---

## <a name="endpoints"></a>4. API ENDPOINTS

### Authentication Required
```
All endpoints require: auth:sanctum, verified, role:admin
```

### Rules Management

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/admin/points-rules` | List all rules |
| GET | `/admin/points-rules/{id}` | Get rule details |
| POST | `/admin/points-rules` | Create rule |
| PUT | `/admin/points-rules/{id}` | Update rule |
| DELETE | `/admin/points-rules/{id}` | Delete rule |

### Violations Management

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/admin/points-rules/violations` | List violations |
| GET | `/admin/points-rules/violations/{id}` | Get violation |
| PUT | `/admin/points-rules/violations/{id}/review` | Review/action violation |

### Fraud Management

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/admin/points-rules/fraud-flags` | List fraud flags |
| GET | `/admin/points-rules/fraud-flags/{id}` | Get flag |
| PUT | `/admin/points-rules/fraud-flags/{id}/investigate` | Investigate fraud |

### Activity Management

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/admin/points-rules/pending-activities` | List pending |
| PUT | `/admin/points-rules/activities/{id}/approve` | Approve activity |
| PUT | `/admin/points-rules/activities/{id}/reject` | Reject activity |

### Notifications

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/admin/points-rules/notifications` | List notifications |
| PUT | `/admin/points-rules/notifications/{id}/read` | Mark as read |
| PUT | `/admin/points-rules/notifications/{id}/action` | Take action |

---

## <a name="integration"></a>5. INTEGRATION EXAMPLES

### A. Integrate with Marketplace Purchase

**File:** `app/Http/Controllers/CheckoutController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Services\PointsRulesEngine;
use App\Models\Order;

class CheckoutController extends Controller
{
    public function applyPointsDiscount(Request $request, PointsRulesEngine $engine)
    {
        $user = auth()->user();
        $order = Order::find($request->order_id);
        
        // Get discount amount from request
        $points_to_use = $request->points_amount;
        
        // Validate redemption with rules engine
        $validation = $engine->validateRedemptionActivity(
            $user,
            $points_to_use,
            [
                'order_id' => $order->id,
                'discount_percent' => $request->discount_percent,
                'transaction_amount' => $order->subtotal
            ]
        );
        
        // If validation failed, reject
        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validation['violations'][0] ?? 'Cannot apply discount'
            ], 422);
        }
        
        // If requires approval, put in pending
        if ($validation['requires_approval']) {
            // Record activity as pending
            $activity = $engine->recordActivity(
                $user,
                'redeemed',
                $points_to_use,
                'pending',
                [
                    'order_id' => $order->id,
                    'discount_amount' => $points_to_use * 10 // Convert points to rupiah
                ]
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Discount pending admin approval',
                'discount_pending' => true
            ]);
        }
        
        // Apply discount immediately
        $discount_amount = $points_to_use * 10; // 1 point = Rp 10 (configurable)
        
        if ($discount_amount > $order->subtotal * 0.5) {
            return response()->json([
                'success' => false,
                'message' => 'Discount exceeds 50% limit'
            ], 422);
        }
        
        // Update order
        $order->discount_from_points = $discount_amount;
        $order->final_price = $order->subtotal - $discount_amount;
        $order->save();
        
        // Record activity as approved
        $activity = $engine->recordActivity(
            $user,
            'redeemed',
            $points_to_use,
            'approved',
            [
                'order_id' => $order->id,
                'discount_amount' => $discount_amount
            ]
        );
        
        // Deduct points from user
        $user->decrement('points', $points_to_use);
        
        // Notify admin
        $engine->notifyAdminOfActivity(
            $activity,
            'discount_used',
            severity: 1
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Discount applied',
            'discount_amount' => $discount_amount,
            'final_price' => $order->final_price
        ]);
    }
}
```

### B. Integrate with Referral System

**File:** `app/Services/ReferralService.php`

```php
<?php

namespace App\Services;

use App\Models\User;
use App\Services\PointsRulesEngine;

class ReferralService
{
    protected $pointsEngine;
    
    public function __construct(PointsRulesEngine $engine)
    {
        $this->pointsEngine = $engine;
    }
    
    public function awardReferralBonus(User $referrer, User $referee)
    {
        // Validate referral with rules engine
        $validation = $this->pointsEngine->validateEarningActivity(
            $referrer,
            [
                'activity_type' => 'referral',
                'referee_id' => $referee->id,
                'referee_ip' => $referee->last_known_ip
            ]
        );
        
        if (!$validation['valid']) {
            // Log fraud detection
            event(new ReferralFraudDetected($referrer, $referee));
            return false;
        }
        
        // Get referral bonus from config
        $bonus = PointsSystemConfig::getValue('referral_bonus', 5000);
        
        // Record activity
        $activity = $this->pointsEngine->recordActivity(
            $referrer,
            'earned',
            $bonus,
            $validation['requires_approval'] ? 'pending' : 'approved',
            ['referee_id' => $referee->id]
        );
        
        // Award points if immediate approval
        if (!$validation['requires_approval']) {
            $referrer->increment('points', $bonus);
        }
        
        return $activity;
    }
}
```

### C. Track Purchase Points Earning

**File:** `app/Models/Order.php`

```php
<?php

namespace App\Models;

use App\Services\PointsRulesEngine;

class Order extends Model
{
    protected static function boot()
    {
        parent::boot();
        
        static::created(function ($order) {
            if ($order->payment_status === 'confirmed') {
                // Award points for purchase
                $engine = app(PointsRulesEngine::class);
                $buyer = $order->buyer;
                
                // Calculate points (1% of purchase amount)
                $points = floor($order->total_price / 100);
                
                // Validate earning
                $validation = $engine->validateEarningActivity($buyer, [
                    'activity_type' => 'purchase',
                    'amount' => $order->total_price
                ]);
                
                if ($validation['valid']) {
                    // Record activity
                    $activity = $engine->recordActivity(
                        $buyer,
                        'earned',
                        $points,
                        'approved',
                        ['order_id' => $order->id]
                    );
                    
                    // Add points
                    $buyer->increment('points', $points);
                }
            }
        });
    }
}
```

---

## <a name="testing"></a>6. TESTING GUIDE

### Unit Tests

**File:** `tests/Unit/PointsRulesEngineTest.php`

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PointsRulesEngine;
use App\Models\User;

class PointsRulesEngineTest extends TestCase
{
    protected $engine;
    protected $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = app(PointsRulesEngine::class);
        $this->user = User::factory()->create(['points' => 10000]);
    }
    
    // Test rapid redemption detection
    public function test_rapid_redemptions_detected()
    {
        // Create 3 redemptions in 1 hour
        for ($i = 0; $i < 3; $i++) {
            $this->engine->recordActivity(
                $this->user,
                'redeemed',
                1000,
                'approved'
            );
        }
        
        // 4th redemption should be flagged
        $result = $this->engine->validateRedemptionActivity(
            $this->user,
            1000,
            ['order_id' => 'test-order']
        );
        
        $this->assertGreater($result['risk_score'], 30);
        $this->assertTrue(in_array('rapid_redemptions', $result['violations']));
    }
    
    // Test duplicate discount detection
    public function test_duplicate_discounts_blocked()
    {
        // Apply first discount
        $order_id = 'order-123';
        $activity1 = $this->engine->recordActivity(
            $this->user,
            'redeemed',
            5000,
            'approved',
            ['order_id' => $order_id]
        );
        
        // Try to apply second discount same order
        $result = $this->engine->validateRedemptionActivity(
            $this->user,
            5000,
            [
                'order_id' => $order_id,
                'duplicate_check' => true
            ]
        );
        
        $this->assertFalse($result['valid']);
        $this->assertTrue(in_array('duplicate_discount', $result['violations']));
    }
    
    // Test IP change detection
    public function test_ip_change_fraud_detection()
    {
        // Record first activity
        $activity1 = $this->engine->recordActivity(
            $this->user,
            'redeemed',
            1000,
            'approved',
            ['ip_address' => '192.168.1.100']
        );
        
        // Immediately change IP (< 60 seconds)
        $fraud = $this->engine->checkFraudPatterns(
            $this->user,
            'redeemed',
            ['ip_address' => '103.27.1.50']
        );
        
        $this->assertTrue($fraud['is_fraud']);
        $this->assertGreater($fraud['risk_score'], 40);
        $this->assertTrue(in_array('impossible_timing', $fraud['flags']));
    }
}
```

### Feature Tests

**File:** `tests/Feature/PointsDiscountIntegrationTest.php`

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;

class PointsDiscountIntegrationTest extends TestCase
{
    protected $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()
            ->has(Order::factory()->count(1))
            ->create(['points' => 100000]);
    }
    
    public function test_apply_points_discount_to_order()
    {
        $order = Order::factory()->create([
            'buyer_id' => $this->user->id,
            'subtotal' => 500000,
            'payment_status' => 'confirmed'
        ]);
        
        $response = $this->actingAs($this->user)->postJson(
            route('checkout.apply-discount'),
            [
                'order_id' => $order->id,
                'points_amount' => 5000,
                'discount_percent' => 10
            ]
        );
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Check order updated
        $order->refresh();
        $this->assertEquals(50000, $order->discount_from_points);
    }
    
    public function test_cannot_exceed_discount_limit()
    {
        $order = Order::factory()->create([
            'buyer_id' => $this->user->id,
            'subtotal' => 100000
        ]);
        
        $response = $this->actingAs($this->user)->postJson(
            route('checkout.apply-discount'),
            [
                'order_id' => $order->id,
                'points_amount' => 10000, // Would be 100000 discount > 50% limit
                'discount_percent' => 100
            ]
        );
        
        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }
}
```

### Test Running

```bash
# Run all tests
php artisan test

# Run specific test class
php artisan test tests/Unit/PointsRulesEngineTest.php

# Run with coverage
php artisan test --coverage

# Run and show output
php artisan test --verbose
```

---

## <a name="troubleshooting"></a>7. TROUBLESHOOTING

### Issue: Points not awarded after purchase

**Check:**
1. Verify payment status is 'confirmed'
2. Check PointsActivity table for 'pending' status
3. Review PointsAdminNotification for any flags
4. Check PointsRuleViolation table for rule breaches

**Solution:**
```php
// Manually trigger points award
$order = Order::find($order_id);
$user = $order->buyer;

$engine = app(PointsRulesEngine::class);
$points = floor($order->total_price / 100);

$activity = $engine->recordActivity(
    $user,
    'earned',
    $points,
    'approved',
    ['order_id' => $order_id]
);

$user->increment('points', $points);
```

### Issue: Discount not being applied

**Check Steps:**
1. User has enough points? `$user->points >= $points_to_redeem`
2. Daily limit exceeded? Check PointsActivity for today
3. Is activity flagged for review? Check status = 'pending'
4. Is there a fraud flag? Check PointsFraudFlag table

**Debug:**
```php
$engine = app(PointsRulesEngine::class);
$result = $engine->validateRedemptionActivity(
    auth()->user(),
    5000,
    ['order_id' => 'test-order']
);

dd($result); // See violations, risk_score, etc
```

### Issue: Fraud flags creating too many false positives

**Solution - Adjust Thresholds:**

```php
// Update config values
PointsSystemConfig::setValue('fraud_ip_threshold', 120); // 2 minutes instead of 1
PointsSystemConfig::setValue('fraud_confidence_high', 90); // Higher threshold
PointsSystemConfig::setValue('rapid_redemption_threshold', 5); // 5 instead of 3 per hour
```

### Issue: Admin notifications not received

**Check:**
1. Is notification type correct?
2. Is admin role assigned to user?
3. Check PointsAdminNotification table for records
4. Verify notification middleware running

**Debug:**
```php
// Manually create notification
PointsAdminNotification::create([
    'user_id' => $admin_id,
    'activity_id' => $activity_id,
    'notification_type' => 'suspicious_activity',
    'severity' => 3,
    'title' => 'Test Notification',
    'message' => 'This is a test'
]);
```

---

## 📚 Related Documentation

- **User Guide:** `POINTS_SYSTEM_RULES.md`
- **API Reference:** See endpoints section above
- **Models Reference:** Check individual model files
- **Database Migrations:** `database/migrations/2025_12_07_create_points_*.php`

---

**Version:** 2.0  
**Last Updated:** December 7, 2025  
**Status:** ✅ Production Ready
