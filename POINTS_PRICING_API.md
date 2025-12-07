# Points Pricing API & Integration Guide

---

## Quick Links

- **Admin Panel:** `http://noteds.test/admin/points-pricing`
- **Monitoring Dashboard:** `http://noteds.test/admin/points-monitoring`
- **Export Report:** `http://noteds.test/admin/points-redemption/export`

---

## REST API Endpoints

### List All Pricing Configurations

```
GET /admin/points-pricing
```

**Permissions:** Admin role required

**Response:** HTML page with table view

**Query Parameters:**
- None

**Example:**
```bash
curl -X GET "http://noteds.test/admin/points-pricing" \
  -H "Authorization: Bearer {TOKEN}"
```

---

### Create New Pricing Configuration

```
POST /admin/points-pricing
```

**Permissions:** Admin role required

**Request Body:**
```json
{
  "name": "10% Discount",
  "type": "discount",
  "points_required": 500,
  "discount_percent": 10,
  "discount_amount": null,
  "daily_limit": 50,
  "user_limit": 2,
  "description": "Get 10% off on your next purchase",
  "is_active": true,
  "expires_at": null
}
```

**Field Descriptions:**
- `name` (string, required): Display name for the offer
- `type` (enum: 'discount' | 'premium_feature', required): Type of redemption
- `points_required` (integer, required): Points needed to redeem
- `discount_percent` (integer, 0-100): Percentage discount (for discount type)
- `discount_amount` (decimal): Rupiah amount discount (for discount type)
- `premium_days` (integer): Days of premium access (for premium_feature type)
- `daily_limit` (integer, nullable): Max redemptions per day
- `user_limit` (integer, nullable): Max redemptions per user
- `description` (text, nullable): Additional description
- `is_active` (boolean): Enable/disable the offer
- `expires_at` (datetime, nullable): Expiration date

**Validation Rules:**
- name: required, string, max:255
- type: required, in:discount,premium_feature
- points_required: required, integer, min:1, max:1000000
- discount_percent: nullable, integer, min:0, max:100
- discount_amount: nullable, numeric, min:0
- premium_days: nullable, integer, min:1
- daily_limit: nullable, integer, min:1
- user_limit: nullable, integer, min:1
- expires_at: nullable, date, after:today

**Response (Success):**
```
HTTP/1.1 302 Found
Location: /admin/points-pricing
```

**Response (Validation Error):**
```
HTTP/1.1 302 Found
Location: /admin/points-pricing/create
(Errors in session)
```

**Example:**
```bash
curl -X POST "http://noteds.test/admin/points-pricing" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "10% Discount",
    "type": "discount",
    "points_required": 500,
    "discount_percent": 10,
    "daily_limit": 50,
    "user_limit": 2,
    "is_active": true
  }'
```

---

### Get Pricing Configuration Details

```
GET /admin/points-pricing/{id}/show
```

**Permissions:** Admin role required

**URL Parameters:**
- `id` (UUID): Configuration ID

**Response:** HTML page with configuration details

**Example:**
```bash
curl -X GET "http://noteds.test/admin/points-pricing/550e8400-e29b-41d4-a716-446655440000/show"
```

---

### Update Pricing Configuration

```
PUT /admin/points-pricing/{id}
```

**Permissions:** Admin role required

**URL Parameters:**
- `id` (UUID): Configuration ID

**Request Body:** (Same as CREATE)

**Response:**
```
HTTP/1.1 302 Found
Location: /admin/points-pricing
```

**Example:**
```bash
curl -X PUT "http://noteds.test/admin/points-pricing/550e8400-e29b-41d4-a716-446655440000" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "15% Discount",
    "type": "discount",
    "points_required": 600,
    "discount_percent": 15,
    "daily_limit": 100,
    "user_limit": 3,
    "is_active": true
  }'
```

---

### Delete Pricing Configuration

```
DELETE /admin/points-pricing/{id}
```

**Permissions:** Admin role required

**URL Parameters:**
- `id` (UUID): Configuration ID

**Response:**
```
HTTP/1.1 302 Found
Location: /admin/points-pricing
```

**Example:**
```bash
curl -X DELETE "http://noteds.test/admin/points-pricing/550e8400-e29b-41d4-a716-446655440000"
```

---

### Get Redemption Monitoring Dashboard

```
GET /admin/points-monitoring
```

**Permissions:** Admin role required

**Query Parameters:**
- `from_date` (date, optional): Start date for filtering (YYYY-MM-DD)
- `to_date` (date, optional): End date for filtering (YYYY-MM-DD)

**Response:** HTML page with monitoring dashboard

**Statistics Displayed:**
- Today's Redemptions: Count of redemptions made today
- Points Used Today: Total points redeemed today
- Weekly Redemptions: Count for current week
- Active Redemptions: Currently valid redemptions

**Example:**
```bash
curl -X GET "http://noteds.test/admin/points-monitoring?from_date=2025-12-01&to_date=2025-12-07"
```

---

### Export Redemption Report

```
GET /admin/points-redemption/export
```

**Permissions:** Admin role required

**Query Parameters:**
- `from_date` (date, optional): Start date (YYYY-MM-DD)
- `to_date` (date, optional): End date (YYYY-MM-DD)

**Response Headers:**
```
Content-Type: text/csv; charset=UTF-8
Content-Disposition: attachment; filename="point-redemptions-2025-12-07.csv"
```

**CSV Columns:**
```
User ID,User Name,Email,Redemption Type,Points Used,Value,Status,Created At
```

**Example:**
```bash
curl -X GET "http://noteds.test/admin/points-redemption/export" \
  -o "redemptions.csv"
```

---

## Model Methods Reference

### PointsPricingConfig Model

#### Static Methods

**getActiveOptions()**
```php
$options = PointsPricingConfig::getActiveOptions();
// Returns Collection of active, non-expired configurations
```

**getActiveByType($type)**
```php
$discounts = PointsPricingConfig::getActiveByType('discount');
$premiums = PointsPricingConfig::getActiveByType('premium_feature');
// Returns Collection filtered by type
```

#### Instance Methods

**isDailyLimitReached()**
```php
$config = PointsPricingConfig::find($id);
if ($config->isDailyLimitReached()) {
    // Daily limit reached, can't redeem today
}
```

**isUserLimitReached($userId)**
```php
$config = PointsPricingConfig::find($id);
if ($config->isUserLimitReached($userId)) {
    // User has reached personal redemption limit
}
```

**getValue()**
```php
$config = PointsPricingConfig::find($id);
$value = $config->getValue();
// Returns discount_amount or (price * discount_percent / 100)
```

**getDisplayNameAttribute()**
```php
$config = PointsPricingConfig::find($id);
echo $config->display_name;
// Outputs: "10% Discount (10%)" or "30 Days Premium (30 days)"
```

---

## Usage Examples

### Example 1: Create a Discount Offer

```php
use App\Models\PointsPricingConfig;

$discount = PointsPricingConfig::create([
    'name' => '5% Off Everything',
    'type' => 'discount',
    'points_required' => 250,
    'discount_percent' => 5,
    'daily_limit' => 100,
    'user_limit' => 3,
    'description' => 'Get 5% off on any purchase',
    'is_active' => true,
]);

// Returns: PointsPricingConfig instance with UUID
```

### Example 2: Get All Active Discount Offers

```php
use App\Models\PointsPricingConfig;

$discounts = PointsPricingConfig::where('type', 'discount')
    ->where('is_active', true)
    ->whereNull('expires_at')
    ->orWhere('expires_at', '>', now())
    ->get();

foreach ($discounts as $discount) {
    echo $discount->display_name;
}
```

### Example 3: Check if User Can Redeem

```php
use App\Models\PointsPricingConfig;
use App\Models\User;

$user = User::find($userId);
$config = PointsPricingConfig::find($configId);

// Check daily limit
if ($config->isDailyLimitReached()) {
    return "Daily redemption limit reached!";
}

// Check user limit
if ($config->isUserLimitReached($user->id)) {
    return "You've reached your personal redemption limit!";
}

// User can redeem
```

### Example 4: Process Redemption

```php
use App\Models\PointsPricingConfig;
use App\Models\PointRedemption;
use App\Models\User;

$user = User::find($userId);
$config = PointsPricingConfig::find($configId);

// Check if can redeem
if ($user->points < $config->points_required) {
    return "Insufficient points!";
}

// Create redemption record
$redemption = PointRedemption::create([
    'user_id' => $user->id,
    'points' => $config->points_required,
    'type' => $config->type,
    'value' => $config->getValue(),
    'status' => 'pending',
]);

// Deduct points
$user->points -= $config->points_required;
$user->save();

return "Redemption successful! Value: " . $config->getValue();
```

### Example 5: Export Monthly Report

```bash
# Export December redemptions
curl -X GET "http://noteds.test/admin/points-redemption/export?from_date=2025-12-01&to_date=2025-12-31" \
  -o "december-redemptions.csv"

# Open in Excel or Google Sheets
```

---

## Authentication & Authorization

### Required Permissions
- All endpoints require **authentication** (logged-in user)
- All endpoints require **admin role**

### Middleware Stack
```php
['auth:sanctum', 'verified', 'role:admin']
```

### How to Obtain Admin Role
```php
use Spatie\Permission\Models\Role;

$user = User::find($userId);
$adminRole = Role::findByName('admin');
$user->assignRole($adminRole);
```

---

## Error Handling

### Common Errors

**401 Unauthorized**
```json
{
  "message": "Unauthenticated."
}
```
Solution: Login first with valid credentials

**403 Forbidden**
```json
{
  "message": "Unauthorized"
}
```
Solution: User must have admin role

**422 Unprocessable Entity**
```json
{
  "errors": {
    "name": ["The name field is required."],
    "type": ["The selected type is invalid."]
  }
}
```
Solution: Check validation rules and provide correct data

**404 Not Found**
```
Configuration not found
```
Solution: Verify the UUID/ID exists

---

## Rate Limiting

Currently no rate limiting is implemented. For production, consider:

```php
// In routes/web.php
Route::middleware(['throttle:60,1'])->group(function () {
    Route::resource('points-pricing', PointsPricingController::class);
});
```

This limits to 60 requests per minute per user.

---

## Webhooks (Future)

Consider implementing webhooks to notify external systems:

```php
// When new redemption occurs
dispatch(new RedemptionCreatedEvent($redemption));

// When config changes
dispatch(new PointsPricingConfigUpdatedEvent($config));
```

---

## Monitoring & Logging

All admin actions are logged in Laravel logs:

```bash
# View logs
tail -f storage/logs/laravel.log | grep "points-pricing"
```

---

## Troubleshooting

### Issue: Configuration not appearing for users
- **Check:** `is_active` is `true`
- **Check:** `expires_at` is NULL or in future
- **Check:** `points_required` is within user's point balance

### Issue: CSV export is empty
- **Check:** Date range includes redemptions
- **Check:** Any redemptions exist in database
- **Check:** File permissions allow write access

### Issue: Forms not validating
- **Check:** All required fields are provided
- **Check:** Enum values are lowercase ('discount', 'premium_feature')
- **Check:** Numeric fields don't contain currency symbols

---

## Best Practices

✅ **DO:**
- Test with development data first
- Monitor redemption patterns daily
- Set expiration dates for promotional offers
- Keep daily limits conservative initially
- Export weekly reports for analysis
- Disable problematic offers immediately

❌ **DON'T:**
- Set limits too high (hurts profitability)
- Leave unlimited daily redemptions
- Forget to set expiration on promotions
- Assume redemption data is accurate without checking
- Delete configuration data (it contains history)

---

## Support

For questions or issues:
1. Check the `POINTS_PRICING_FEATURE.md` documentation
2. Review test cases in `tests/Feature/PointsPricingTest.php`
3. Check Laravel logs in `storage/logs/laravel.log`
4. Contact development team

---

**Last Updated:** December 7, 2025  
**Version:** 1.0  
**Status:** Production Ready ✅
