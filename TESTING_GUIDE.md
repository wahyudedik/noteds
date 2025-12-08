# Testing Guide: Localization, Currency & Fraud Detection

## Unit Tests

### Test Fraud Detection Service

```php
// tests/Unit/FraudDetectionServiceTest.php

namespace Tests\Unit;

use App\Models\AffiliateFraudLog;
use App\Models\User;
use App\Services\FraudDetectionService;
use Tests\TestCase;

class FraudDetectionServiceTest extends TestCase
{
    private FraudDetectionService $fraudDetectionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fraudDetectionService = app(FraudDetectionService::class);
    }

    public function test_detect_multiple_accounts_from_same_device()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $deviceFingerprint = AffiliateFraudLog::generateDeviceFingerprint(
            '192.168.1.1',
            'Mozilla/5.0'
        );

        $user1->update(['device_fingerprint' => $deviceFingerprint]);
        $user2->update(['device_fingerprint' => $deviceFingerprint]);

        $result = $this->fraudDetectionService->detectAffiliateClickFraud(
            $user1,
            '192.168.1.1',
            'Mozilla/5.0'
        );

        $this->assertContains('multiple_accounts', $result['fraud_indicators']);
    }

    public function test_rapid_conversion_detection()
    {
        $affiliate = User::factory()->create();

        // Create multiple conversions
        for ($i = 0; $i < 12; $i++) {
            AffiliateFraudLog::logActivity(
                affiliateId: $affiliate->id,
                converterId: null,
                activityType: 'conversion',
                ipAddress: '192.168.1.1',
                userAgent: 'Mozilla/5.0',
                fraudIndicators: [],
                metadata: ['amount' => 100000]
            );
        }

        $result = $this->fraudDetectionService->detectAffiliateClickFraud(
            $affiliate,
            '192.168.1.1',
            'Mozilla/5.0'
        );

        $this->assertContains('rapid_conversions', $result['fraud_indicators']);
    }

    public function test_risk_score_calculation()
    {
        $indicators = ['multiple_accounts', 'vpn_proxy'];
        $score = AffiliateFraudLog::calculateRiskScore($indicators);

        $this->assertGreaterThan(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    public function test_fraud_log_creation()
    {
        $user = User::factory()->create();
        
        $log = AffiliateFraudLog::logActivity(
            affiliateId: $user->id,
            converterId: null,
            activityType: 'click',
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0',
            fraudIndicators: ['multiple_accounts'],
            notes: 'Test fraud log',
            metadata: ['test' => true]
        );

        $this->assertTrue($log->exists);
        $this->assertEquals('click', $log->activity_type);
        $this->assertContains('multiple_accounts', $log->fraud_indicators);
    }
}
```

### Test Currency Service

```php
// tests/Unit/CurrencyServiceTest.php

namespace Tests\Unit;

use App\Services\CurrencyService;
use Tests\TestCase;

class CurrencyServiceTest extends TestCase
{
    private CurrencyService $currencyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->currencyService = app(CurrencyService::class);
    }

    public function test_currency_conversion_usd_to_idr()
    {
        $result = $this->currencyService->convert(100, 'USD', 'IDR');
        
        $this->assertIsFloat($result);
        $this->assertGreaterThan(1000000, $result); // ~1.6M
    }

    public function test_same_currency_returns_same_amount()
    {
        $result = $this->currencyService->convert(100, 'USD', 'USD');
        
        $this->assertEquals(100, $result);
    }

    public function test_format_currency_with_symbol()
    {
        $formatted = $this->currencyService->format(100000, 'IDR');
        
        $this->assertStringContainsString('Rp', $formatted);
        $this->assertStringContainsString('100', $formatted);
    }

    public function test_format_api_response()
    {
        $response = $this->currencyService->formatForApi(100, 'USD');
        
        $this->assertArrayHasKey('amount', $response);
        $this->assertArrayHasKey('currency', $response);
        $this->assertArrayHasKey('symbol', $response);
        $this->assertArrayHasKey('formatted', $response);
    }

    public function test_validate_currency()
    {
        $this->assertTrue($this->currencyService->isValidCurrency('USD'));
        $this->assertFalse($this->currencyService->isValidCurrency('XXX'));
    }
}
```

### Test Locale Service

```php
// tests/Unit/LocaleServiceTest.php

namespace Tests\Unit;

use App\Models\User;
use App\Services\LocaleService;
use Tests\TestCase;

class LocaleServiceTest extends TestCase
{
    private LocaleService $localeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->localeService = app(LocaleService::class);
    }

    public function test_set_user_locale()
    {
        $user = User::factory()->create();
        
        $result = $this->localeService->setUserLocale($user, 'id');
        
        $this->assertTrue($result);
        $this->assertEquals('id', $user->refresh()->locale);
    }

    public function test_get_user_locale()
    {
        $user = User::factory()->create(['locale' => 'id']);
        
        $locale = $this->localeService->getUserLocale($user);
        
        $this->assertEquals('id', $locale);
    }

    public function test_format_date_indonesian()
    {
        $user = User::factory()->create(['locale' => 'id']);
        $date = \Carbon\Carbon::create(2025, 12, 15, 14, 30, 0);
        
        $formatted = $this->localeService->formatDate($date, $user);
        
        $this->assertStringContainsString('Desember', $formatted);
    }

    public function test_invalid_locale_throws_exception()
    {
        $user = User::factory()->create();
        
        $this->expectException(\InvalidArgumentException::class);
        
        $this->localeService->setUserLocale($user, 'invalid');
    }

    public function test_get_full_settings()
    {
        $user = User::factory()->create([
            'locale' => 'id',
            'timezone' => 'Asia/Jakarta',
            'currency' => 'IDR',
        ]);
        
        $settings = $this->localeService->getFullSettings($user);
        
        $this->assertEquals('id', $settings['locale']);
        $this->assertEquals('Asia/Jakarta', $settings['timezone']);
        $this->assertEquals('IDR', $settings['currency']);
    }
}
```

## Feature Tests

### Test User Settings API

```php
// tests/Feature/UserSettingsTest.php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class UserSettingsTest extends TestCase
{
    public function test_user_can_get_settings()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->getJson('/api/user/settings');
        
        $response->assertOk()
            ->assertJsonStructure([
                'locale',
                'currency',
                'all_locales',
                'all_timezones',
                'all_currencies',
            ]);
    }

    public function test_user_can_update_settings()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->postJson('/api/user/settings', [
                'locale' => 'id',
                'timezone' => 'Asia/Jakarta',
                'currency' => 'IDR',
            ]);
        
        $response->assertOk()
            ->assertJsonPath('message', 'Settings updated successfully');
        
        $this->assertEquals('id', $user->refresh()->locale);
        $this->assertEquals('Asia/Jakarta', $user->refresh()->timezone);
        $this->assertEquals('IDR', $user->refresh()->currency);
    }

    public function test_invalid_locale_validation()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->postJson('/api/user/settings', [
                'locale' => 'invalid',
            ]);
        
        $response->assertUnprocessable()
            ->assertJsonValidationErrors('locale');
    }

    public function test_unauthenticated_cannot_access_settings()
    {
        $response = $this->getJson('/api/user/settings');
        
        $response->assertUnauthorized();
    }
}
```

### Test Affiliate Click Tracking

```php
// tests/Feature/AffiliateClickTrackingTest.php

namespace Tests\Feature;

use App\Models\Affiliate;
use App\Models\User;
use Tests\TestCase;

class AffiliateClickTrackingTest extends TestCase
{
    public function test_track_affiliate_click()
    {
        $affiliate = Affiliate::factory()->create();
        
        $response = $this->postJson(
            "/api/affiliate/{$affiliate->code}/track-click"
        );
        
        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'click_id',
                'affiliate_id',
                'fraud_risk',
            ]);
    }

    public function test_invalid_affiliate_code()
    {
        $response = $this->postJson('/api/affiliate/INVALID_CODE/track-click');
        
        $response->assertNotFound();
    }

    public function test_track_conversion_after_click()
    {
        $user = User::factory()->create();
        $affiliate = Affiliate::factory()->create();
        
        // Track click
        $clickResponse = $this->postJson(
            "/api/affiliate/{$affiliate->code}/track-click"
        );
        $clickId = $clickResponse->json('click_id');
        
        // Track conversion
        $response = $this->actingAs($user)
            ->postJson('/api/affiliate/track-conversion', [
                'click_id' => $clickId,
                'amount' => 500000,
                'product_id' => \Str::uuid(),
            ]);
        
        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'conversion_id',
                'fraud_risk',
                'amount',
            ]);
    }

    public function test_expired_click_rejected()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->postJson('/api/affiliate/track-conversion', [
                'click_id' => \Str::uuid(),
                'amount' => 500000,
                'product_id' => \Str::uuid(),
            ]);
        
        $response->assertNotFound();
    }
}
```

## Integration Tests

### Test Fraud Detection Flow

```php
// tests/Integration/FraudDetectionFlowTest.php

namespace Tests\Integration;

use App\Models\Affiliate;
use App\Models\AffiliateFraudLog;
use App\Models\User;
use Tests\TestCase;

class FraudDetectionFlowTest extends TestCase
{
    public function test_full_fraud_detection_flow()
    {
        // Create users
        $affiliate = User::factory()->create();
        $converter = User::factory()->create();
        
        // Create affiliate
        $affiliateRecord = Affiliate::factory()
            ->create(['user_id' => $affiliate->id]);
        
        // Simulate multiple clicks from same device
        for ($i = 0; $i < 3; $i++) {
            $this->postJson(
                "/api/affiliate/{$affiliateRecord->code}/track-click",
                [],
                ['X-Forwarded-For' => '192.168.1.1']
            );
        }
        
        // Verify fraud logs created
        $logs = AffiliateFraudLog::where('affiliate_id', $affiliate->id)->get();
        $this->assertGreaterThan(0, $logs->count());
    }

    public function test_fraud_flags_user_on_high_risk()
    {
        $affiliate = User::factory()->create();
        $affiliateRecord = Affiliate::factory()
            ->create(['user_id' => $affiliate->id]);
        
        // Create high-risk scenario
        for ($i = 0; $i < 15; $i++) {
            AffiliateFraudLog::logActivity(
                affiliateId: $affiliate->id,
                converterId: null,
                activityType: 'conversion',
                ipAddress: '192.168.1.1',
                userAgent: 'Mozilla/5.0',
                fraudIndicators: ['rapid_conversions'],
            );
        }
        
        // Verify user is flagged
        $this->assertTrue($affiliate->refresh()->is_fraud_suspected);
    }
}
```

## Manual Testing Checklist

- [ ] Test click tracking endpoint dengan berbagai IP addresses
- [ ] Test conversion tracking dengan valid click_id
- [ ] Test conversion tracking dengan expired click_id
- [ ] Test fraud detection dengan multiple accounts
- [ ] Test user settings update untuk semua locales
- [ ] Test currency conversion antara currencies
- [ ] Test date formatting untuk semua locales
- [ ] Test timezone conversion untuk user events
- [ ] Verify database constraints dan indexes
- [ ] Test API authentication/authorization
- [ ] Test error handling dan validation messages
- [ ] Verify fraud logs created correctly
- [ ] Test admin fraud dashboard (if exists)

## Performance Testing

### Load Testing Affiliate Clicks

```bash
# Using Apache Bench
ab -n 1000 -c 10 "http://localhost/api/affiliate/CODE_123/track-click"

# Expected: > 500 requests/sec
```

### Database Query Performance

- Verify indexes on `affiliate_fraud_logs.ip_address`
- Verify indexes on `affiliate_fraud_logs.is_flagged`
- Verify indexes on `affiliate_fraud_logs.created_at`
- Verify indexes on `users.device_fingerprint`

### Cache Performance

- Verify fraud detection results cached appropriately
- Verify locale/timezone settings cached per user
- Check cache hit rates menggunakan Laravel Debugbar

## Security Testing

- [ ] Test SQL injection attempts dalam request parameters
- [ ] Test XSS attempts dalam metadata fields
- [ ] Verify CSRF protection on POST endpoints
- [ ] Test rate limiting on public endpoints
- [ ] Verify authentication tokens expired correctly
- [ ] Test authorization for protected endpoints
- [ ] Verify user cannot access other users' data
- [ ] Test fraud indicator injection attempts

## Deployment Checklist

1. Run migrations:
   ```bash
   php artisan migrate
   ```

2. Clear caches:
   ```bash
   php artisan cache:clear
   php artisan config:cache
   ```

3. Register routes in `routes/api.php`:
   ```php
   require __DIR__ . '/api_localization_fraud.php';
   ```

4. Register middleware in `app/Http/Kernel.php`:
   ```php
   protected $routeMiddleware = [
       ...
       'set_locale' => \App\Http\Middleware\SetUserLocale::class,
   ];
   ```

5. Add middleware to routes:
   ```php
   Route::middleware(['auth:sanctum', 'set_locale'])->group(function () {
       // Protected routes
   });
   ```

6. Run tests:
   ```bash
   php artisan test
   ```

7. Monitor fraud logs:
   ```php
   \Log::info('Fraud detection active');
   ```
