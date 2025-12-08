# Implementation Guide: Localization, Currency & Fraud Detection

## Quick Start

### 1. Database Setup

```bash
# Run migrations
php artisan migrate

# Created tables:
# - affiliate_fraud_logs
# - affiliates (new)
# - Updated users with new columns
```

### 2. Register Routes

Add this ke `routes/api.php`:

```php
<?php

use Illuminate\Support\Facades\Route;

// Include localization dan fraud detection routes
require __DIR__ . '/api_localization_fraud.php';

// Other routes...
```

### 3. Register Middleware

Tambah ke `app/Http/Kernel.php` dalam `$routeMiddleware`:

```php
protected $routeMiddleware = [
    // ... existing middleware
    'set_locale' => \App\Http\Middleware\SetUserLocale::class,
];
```

### 4. Register Services (Optional)

Services sudah auto-discovered oleh Laravel, tapi untuk explicit registration, add ke `config/app.php`:

```php
'providers' => [
    // ... existing providers
    App\Providers\LocalizationServiceProvider::class,
];
```

### 5. Configuration

Create atau update `config/currency.php`:

```php
<?php

return [
    'base_currency' => env('CURRENCY_BASE', 'USD'),
    'cache_ttl' => env('CURRENCY_CACHE_TTL', 3600),
    'supported_currencies' => ['USD', 'EUR', 'IDR', 'GBP', 'JPY'],
];
```

### 6. Environment Setup

Add ke `.env`:

```env
# Localization
APP_LOCALE=en
APP_TIMEZONE=UTC

# Currency
CURRENCY_BASE=USD
CURRENCY_CACHE_TTL=3600

# VPN Detection (if using external service)
VPN_CHECK_API_KEY=your_api_key
```

## File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AffiliateClickController.php (NEW)
│   │   └── UserSettingsController.php (NEW)
│   └── Middleware/
│       └── SetUserLocale.php (NEW)
├── Models/
│   ├── AffiliateFraudLog.php (NEW)
│   └── Affiliate.php (NEW)
└── Services/
    ├── FraudDetectionService.php (NEW)
    ├── CurrencyService.php (UPDATED)
    └── LocaleService.php (NEW)

database/
└── migrations/
    ├── 2025_12_09_create_affiliate_fraud_logs_table.php (NEW)
    ├── 2025_12_10_add_locale_fraud_columns_to_users_table.php (NEW)
    └── 2025_12_11_create_affiliates_table.php (NEW)

routes/
└── api_localization_fraud.php (NEW)

config/
└── currency.php (OPTIONAL)

Documentation/
├── API_DOCUMENTATION.md (NEW)
└── TESTING_GUIDE.md (NEW)
```

## Usage Examples

### Example 1: Update User Settings

```php
// In controller or service
use App\Services\CurrencyService;
use App\Services\LocaleService;

class UserController
{
    public function updateSettings(Request $request, LocaleService $localeService, CurrencyService $currencyService)
    {
        $user = $request->user();
        
        $localeService->setUserSettings($user, [
            'locale' => 'id',
            'timezone' => 'Asia/Jakarta',
        ]);
        
        $currencyService->setUserCurrency($user, 'IDR');
        
        return response()->json(['message' => 'Settings updated']);
    }
}
```

### Example 2: Format Amount untuk User

```php
use App\Services\CurrencyService;

class ProductController
{
    public function show(Product $product, Request $request, CurrencyService $currencyService)
    {
        $user = $request->user();
        
        $price = 100; // Base price in USD
        $userCurrency = $currencyService->getUserCurrency($user);
        
        // Convert to user's currency
        $convertedPrice = $currencyService->convert($price, 'USD', $userCurrency);
        
        // Format untuk display
        $formatted = $currencyService->format($convertedPrice, $userCurrency);
        
        return response()->json([
            'product' => $product,
            'price' => $formatted,
            'raw_amount' => $convertedPrice,
            'currency' => $userCurrency,
        ]);
    }
}
```

### Example 3: Track Affiliate Click

```javascript
// Frontend JavaScript
async function trackAffiliateClick(affiliateCode) {
    const response = await fetch(`/api/affiliate/${affiliateCode}/track-click`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    });
    
    const data = await response.json();
    
    // Store click_id untuk tracking conversion nanti
    sessionStorage.setItem('click_id', data.click_id);
    
    return data;
}
```

### Example 4: Track Conversion

```javascript
// Frontend JavaScript
async function trackConversion(amount, productId) {
    const clickId = sessionStorage.getItem('click_id');
    
    const response = await fetch('/api/affiliate/track-conversion', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify({
            click_id: clickId,
            amount: amount,
            product_id: productId,
        })
    });
    
    return await response.json();
}
```

### Example 5: Format Date dengan User's Timezone

```php
use App\Services\LocaleService;

class OrderController
{
    public function show(Order $order, Request $request, LocaleService $localeService)
    {
        $user = $request->user();
        
        // Format date dengan user's locale dan timezone
        $formattedDate = $localeService->formatDate($order->created_at, $user);
        
        return response()->json([
            'order' => $order,
            'created_at' => $formattedDate,
        ]);
    }
}
```

## Fraud Detection Examples

### Example 1: Manual Fraud Check

```php
use App\Services\FraudDetectionService;

class PaymentController
{
    public function processPayment(Request $request, FraudDetectionService $fraudDetectionService)
    {
        $user = $request->user();
        
        // Log dan check fraud
        $fraudLog = $fraudDetectionService->logAndDetectFraud(
            affiliate: null,
            converter: $user,
            activityType: 'conversion',
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            metadata: [
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
            ]
        );
        
        // Check if transaction should be allowed
        if ($fraudLog->is_flagged && $fraudLog->risk_score >= 80) {
            return response()->json([
                'error' => 'Transaction declined - security review required'
            ], 403);
        }
        
        // Process payment...
    }
}
```

### Example 2: Admin Fraud Dashboard

```php
use App\Services\FraudDetectionService;
use App\Models\AffiliateFraudLog;

class FraudDashboardController
{
    public function index(FraudDetectionService $fraudDetectionService)
    {
        return response()->json([
            'summary' => $fraudDetectionService->getFraudSummary(),
            'recent_frauds' => AffiliateFraudLog::where('is_flagged', true)
                ->orderByDesc('created_at')
                ->limit(20)
                ->get(),
            'high_risk_users' => User::where('is_fraud_suspected', true)
                ->get(),
        ]);
    }
}
```

## Testing

### Run Unit Tests

```bash
# All tests
php artisan test

# Specific test file
php artisan test tests/Unit/FraudDetectionServiceTest.php

# With coverage
php artisan test --coverage
```

### Manual API Testing

```bash
# Get user settings
curl -H "Authorization: Bearer TOKEN" \
  http://localhost/api/user/settings

# Update settings
curl -X POST http://localhost/api/user/settings \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"locale":"id","timezone":"Asia/Jakarta"}'

# Track affiliate click
curl -X POST http://localhost/api/affiliate/AFFILIATE_CODE/track-click

# Track conversion
curl -X POST http://localhost/api/affiliate/track-conversion \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "click_id":"550e8400-e29b-41d4-a716-446655440000",
    "amount":500000,
    "product_id":"550e8400-e29b-41d4-a716-446655440001"
  }'
```

## Monitoring & Logging

### View Fraud Logs

```php
// In Tinker
php artisan tinker
> AffiliateFraudLog::where('is_flagged', true)->latest()->get();

// Get fraud summary
> app(FraudDetectionService::class)->getFraudSummary();
```

### Monitor Logs

```bash
# Real-time log monitoring
tail -f storage/logs/laravel.log | grep "fraud"

# Or using artisan tail
php artisan tail --filter=fraud
```

### Database Queries to Monitor

```sql
-- Check flagged frauds
SELECT * FROM affiliate_fraud_logs WHERE is_flagged = 1 ORDER BY created_at DESC;

-- Check suspicious users
SELECT * FROM users WHERE is_fraud_suspected = 1;

-- Fraud summary by type
SELECT activity_type, COUNT(*) as count FROM affiliate_fraud_logs 
WHERE is_flagged = 1 GROUP BY activity_type;

-- Check fraud indicators
SELECT * FROM affiliate_fraud_logs 
WHERE JSON_CONTAINS(fraud_indicators, '"multiple_accounts"');
```

## Performance Optimization

### Add Database Indexes

```sql
-- Already in migration, but verify:
CREATE INDEX idx_affiliate_fraud_logs_ip ON affiliate_fraud_logs(ip_address);
CREATE INDEX idx_affiliate_fraud_logs_flagged ON affiliate_fraud_logs(is_flagged);
CREATE INDEX idx_affiliate_fraud_logs_timestamp ON affiliate_fraud_logs(created_at);
CREATE INDEX idx_users_device_fingerprint ON users(device_fingerprint);
```

### Cache Configuration

```php
// In config/cache.php
'default' => env('CACHE_DRIVER', 'redis'), // Use Redis for production

// Cache TTL settings
CACHE_TTL=3600 // 1 hour untuk fraud detection cache
```

### Queue Jobs (Optional)

```php
// Create job untuk process fraud detection asynchronously
php artisan make:job ProcessFraudDetection

// In job
class ProcessFraudDetection implements ShouldQueue
{
    public function handle(FraudDetectionService $fraudDetectionService)
    {
        // Process fraud detection
    }
}
```

## Troubleshooting

### Fraud Logs Not Created

```php
// Check if middleware is registered
php artisan route:list | grep track

// Check service is instantiated
app(FraudDetectionService::class)->logAndDetectFraud(...);

// Verify database connection
php artisan tinker
> DB::connection()->getDatabaseName();
```

### User Locale Not Changed

```php
// Check if user's locale column exists
php artisan tinker
> User::first()->getAttribute('locale');

// Check if middleware is applied
// Add to route middleware group

// Clear cache
php artisan cache:clear
```

### High Risk Scores Not Detecting Fraud

```php
// Verify calculation weights
> AffiliateFraudLog::calculateRiskScore(['multiple_accounts', 'vpn_proxy']);

// Check if thresholds are correct (60+ is flagged)
```

## Security Considerations

1. **Rate Limiting**: Add rate limiting untuk affiliate click endpoint
   ```php
   Route::post('/affiliate/{code}/track-click', ...)->middleware('throttle:1000,1'); // 1000 per minute
   ```

2. **IP Validation**: Implement geo-IP verification untuk impossible locations

3. **Device Fingerprinting**: Use more sophisticated fingerprinting library
   ```bash
   composer require fingerprint/fingerprint-api
   ```

4. **VPN Detection**: Integrate with VPN detection service
   ```bash
   composer require ipqualityscore/ipqualityscore-php
   ```

5. **Encryption**: Encrypt sensitive data dalam fraud_logs
   ```php
   protected $casts = [
       'fraud_indicators' => 'encrypted:json',
   ];
   ```

## Deployment Checklist

- [ ] Run migrations `php artisan migrate --force`
- [ ] Clear config cache `php artisan config:cache`
- [ ] Clear route cache `php artisan route:cache`
- [ ] Set proper permissions `chmod -R 775 storage bootstrap`
- [ ] Verify database connections working
- [ ] Test endpoints with real data
- [ ] Setup monitoring/alerting untuk fraud
- [ ] Configure VPN/Proxy detection service if using
- [ ] Setup daily backup of fraud_logs table
- [ ] Document API endpoints untuk team
- [ ] Train team on fraud investigation process

## Next Steps

1. Integrate dengan payment gateway untuk real conversions
2. Add admin fraud dashboard UI
3. Implement payout system untuk affiliates
4. Add email notifications untuk suspicious activities
5. Create fraud appeal process untuk users
6. Setup automated fraud report generation
7. Integrate dengan external VPN/Proxy databases
8. Add machine learning untuk fraud detection improvement
