# Quick Reference Guide

## Services Location & Usage

### LocaleService
```php
use App\Services\LocaleService;

$service = app(LocaleService::class);

// Get/Set locale
$service->getUserLocale($user);
$service->setUserLocale($user, 'id');

// Get/Set timezone
$service->getUserTimezone($user);
$service->setUserTimezone($user, 'Asia/Jakarta');

// Get all settings
$service->getUserSettings($user);
$service->getFullSettings($user);

// Format date
$service->formatDate($date, $user);

// Validate
$service->isValidLocale('id');
$service->isValidTimezone('Asia/Jakarta');
```

### CurrencyService
```php
use App\Services\CurrencyService;

$service = app(CurrencyService::class);

// Get/Set currency
$service->getUserCurrency($user);
$service->setUserCurrency($user, 'IDR');

// Convert
$service->convert(100, 'USD', 'IDR');
$service->convertToBase(100, 'IDR');
$service->convertFromBase(100, 'IDR');

// Format
$service->format(100000, 'IDR');
$service->formatForApi(100000, 'IDR');

// Get info
$service->getCurrencySymbol('IDR');
$service->getSymbol('IDR');
$service->getName('IDR');
$service->getSupportedCurrencies();

// Validate
$service->isValidCurrency('IDR');
```

### FraudDetectionService
```php
use App\Services\FraudDetectionService;

$service = app(FraudDetectionService::class);

// Log dengan fraud detection
$log = $service->logAndDetectFraud(
    affiliate: $user,
    converter: null,
    activityType: 'click',
    ipAddress: $request->ip(),
    userAgent: $request->userAgent(),
    metadata: []
);

// Deteksi affiliate fraud
$result = $service->detectAffiliateClickFraud($affiliate, '192.168.1.1', 'Mozilla/5.0');
// Returns: ['fraud_indicators' => [...], 'is_suspicious' => bool]

// Deteksi converter fraud
$result = $service->detectConverterFraud($converter, '192.168.1.1', 'Mozilla/5.0', ['amount' => 500000]);

// Get fraud summary
$summary = $service->getFraudSummary();
// Returns: ['flagged_logs_24h' => int, 'high_risk_users' => int, ...]
```

## Models

### User (Enhanced)
```php
// New attributes
$user->locale; // 'en', 'id', 'ar'
$user->timezone; // 'UTC', 'Asia/Jakarta', etc.
$user->currency; // 'USD', 'IDR', etc.
$user->last_ip_address;
$user->last_user_agent;
$user->device_fingerprint;
$user->is_fraud_suspected;
$user->fraud_notes;
```

### Affiliate
```php
$affiliate = Affiliate::where('code', $code)->first();
$affiliate->user; // Relationship
$affiliate->code;
$affiliate->commission_rate;
$affiliate->total_clicks;
$affiliate->total_conversions;
$affiliate->total_earned;
$affiliate->is_active;
```

### AffiliateFraudLog
```php
$log = AffiliateFraudLog::find($id);
$log->affiliate; // Relationship
$log->converter; // Relationship
$log->ip_address;
$log->fraud_indicators; // JSON array
$log->risk_score;
$log->is_flagged;
$log->metadata; // JSON

// Helper methods
$log->isFraudulent(); // bool
$log->getFraudDescription(); // string
```

## API Endpoints Summary

| Method | Endpoint | Auth | Returns |
|--------|----------|------|---------|
| GET | `/api/user/settings` | Yes | User preferences |
| POST | `/api/user/settings` | Yes | Updated settings |
| POST | `/api/affiliate/{code}/track-click` | No | click_id, fraud_risk |
| POST | `/api/affiliate/track-conversion` | Yes | conversion_id, fraud_risk |

## Database Queries

### Find suspicious users
```php
User::where('is_fraud_suspected', true)->get();
```

### Get recent fraud logs
```php
AffiliateFraudLog::where('is_flagged', true)
    ->latest()
    ->limit(10)
    ->get();
```

### Get fraud summary
```php
AffiliateFraudLog::select('activity_type')
    ->selectRaw('COUNT(*) as count')
    ->where('is_flagged', true)
    ->groupBy('activity_type')
    ->get();
```

### Find users with same device
```php
$fingerprint = 'hash_here';
User::where('device_fingerprint', $fingerprint)->get();
```

### Get top fraud indicators
```php
AffiliateFraudLog::where('is_flagged', true)
    ->get()
    ->flatMap(fn($log) => $log->fraud_indicators ?? [])
    ->countBy()
    ->sort();
```

## Middleware Usage

```php
// In route group
Route::middleware(['auth:sanctum', 'set_locale'])->group(function () {
    // Routes here automatically have user locale set
});
```

## Error Codes Reference

| Code | Message |
|------|---------|
| 400 | Invalid request parameters |
| 401 | Unauthorized/Not authenticated |
| 403 | Transaction declined (fraud) |
| 404 | Resource not found |
| 422 | Validation failed |
| 500 | Server error |

## Common Scenarios

### Scenario 1: User Updates Currency

```php
// Frontend
POST /api/user/settings
{
    "currency": "IDR"
}

// Backend automatically:
// 1. Updates user->currency
// 2. Clears locale cache
// 3. Returns updated settings
```

### Scenario 2: Track Affiliate Click

```php
// Frontend
POST /api/affiliate/AFFILIATE_CODE/track-click

// Backend:
// 1. Logs activity
// 2. Detects fraud indicators
// 3. Calculates risk score
// 4. Stores in cache for 24h
// 5. Returns click_id

// Response
{
    "click_id": "uuid",
    "fraud_risk": 15
}
```

### Scenario 3: Track Conversion

```php
// Frontend
POST /api/affiliate/track-conversion
{
    "click_id": "uuid_from_click",
    "amount": 500000,
    "product_id": "uuid"
}

// Backend:
// 1. Retrieves click from cache
// 2. Detects converter fraud
// 3. Logs conversion
// 4. Clears click from cache
// 5. Returns conversion_id

// Response
{
    "success": true,
    "conversion_id": "uuid",
    "fraud_risk": 20
}
```

## Fraud Risk Interpretation

| Risk Score | Status | Action |
|-----------|--------|--------|
| 0-30 | ✅ Low | Allow |
| 30-60 | ⚠️ Medium | Monitor |
| 60-80 | 🚨 High | Verify |
| 80-100 | 🛑 Critical | Decline |

## Supported Values

### Locales
```
'en' => English
'id' => Indonesian  
'ar' => Arabic
```

### Timezones
```
UTC, America/New_York, America/Chicago, America/Denver,
America/Los_Angeles, Europe/London, Europe/Paris,
Asia/Jakarta, Asia/Bangkok, Asia/Singapore, Asia/Tokyo,
Asia/Dubai, Australia/Sydney
```

### Currencies
```
USD, EUR, IDR, GBP, JPY, AUD, CAD, SGD, MYR, THB, PHP, VND, SAR, AED
```

## Quick Debugging

### Check if user has fraud flag
```php
User::find($id)->is_fraud_suspected;
```

### Get user's settings
```php
$user->load(['locale', 'timezone', 'currency']);
```

### Count flagged logs today
```php
AffiliateFraudLog::where('is_flagged', true)
    ->whereDate('created_at', today())
    ->count();
```

### Clear fraud flag on user
```php
User::find($id)->update([
    'is_fraud_suspected' => false,
    'fraud_notes' => null
]);
```

### Get fraud log details
```php
$log = AffiliateFraudLog::find($id);
$log->getFraudDescription();
$log->fraud_indicators;
$log->risk_score;
```

## Cache Keys

```
user_locale_{user_id}         # 1 hour
user_timezone_{user_id}       # 1 hour
user_currency_{user_id}       # Not cached
click_{click_id}              # 24 hours
vpn_check_{ip_address}        # 24 hours
currency_rates                # 1 hour
```

## Useful Commands

```bash
# Run tests
php artisan test

# Run specific test
php artisan test tests/Unit/FraudDetectionServiceTest.php

# Clear all caches
php artisan cache:clear

# Clear specific cache
php artisan cache:forget user_locale_123

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Tinker session
php artisan tinker
> User::find(1)->currency;
> AffiliateFraudLog::latest()->first();
> exit
```

## Important Notes

1. **Device Fingerprint**: SHA-256(IP + User-Agent)
2. **Risk Score**: Capped at 100, minimum 0
3. **Click Expiry**: 24 hours from creation
4. **Fraud Flag**: Automatic at risk_score >= 60
5. **Currency Conversion**: Uses exchange rate config
6. **Locale**: Applied globally via middleware
7. **Timezone**: Only affects date formatting
8. **Caching**: Redis recommended for production

## Performance Tips

1. Use indexes on high-query columns
2. Cache user preferences (1 hour TTL)
3. Batch fraud detection checks
4. Use async jobs untuk heavy processing
5. Optimize exchange rate updates
6. Monitor database slow queries

---

**Last Updated**: December 2025
**Version**: 1.0
