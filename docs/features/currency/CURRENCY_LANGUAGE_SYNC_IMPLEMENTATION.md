# Currency-Language Integration: Implementation Guide

**Status:** Ready for Implementation  
**Estimated Time:** 3-5 days including testing  
**Risk Level:** LOW (backward compatible changes)

---

## IMPLEMENTATION STEPS

### Step 1: Update CurrencyService (30 minutes)

**File:** `app/Services/CurrencyService.php`

**Add this new method after `getBaseCurrency()` (around line 20):**

```php
/**
 * Get default currency for a specific locale
 * 
 * Maps each language to its appropriate currency:
 * - en (English) → USD
 * - id (Indonesian) → IDR  
 * - ar (Arabic) → AED (UAE Dirham)
 * 
 * @param string|null $locale
 * @return string
 */
public function getDefaultCurrencyForLocale(?string $locale = null): string
{
    $locale = $locale ?? app()->getLocale();
    
    return match ($locale) {
        'en' => 'USD',
        'id' => 'IDR',
        'ar' => 'AED',
        default => $this->baseCurrency,
    };
}
```

**Also update fallback rates in `getRate()` method (around line 100):**

Current:
```php
$fallbacks = [
    'USD' => ['IDR' => 15000],
    'IDR' => ['USD' => 1 / 15000],
];
```

Change to:
```php
$fallbacks = [
    'USD' => ['IDR' => 15500, 'AED' => 3.67],
    'IDR' => ['USD' => 1 / 15500, 'AED' => 1 / 4230],
    'AED' => ['USD' => 1 / 3.67, 'IDR' => 4230],
];
```

---

### Step 2: Update CurrencyHelper (20 minutes)

**File:** `app/Helpers/CurrencyHelper.php`

**Update `getDefaultCurrency()` method (around line 57):**

Current:
```php
public static function getDefaultCurrency(): string
{
    if (auth()->check() && auth()->user()->currency) {
        return auth()->user()->currency;
    }

    if (session()->has('currency')) {
        return session('currency');
    }

    $locale = app()->getLocale();

    return match ($locale) {
        'id' => 'IDR',
        'ar' => 'USD',    // ← CHANGE THIS
        'en' => 'USD',
        default => 'IDR',
    };
}
```

Change to:
```php
public static function getDefaultCurrency(): string
{
    // Check if user has stored preference
    if (auth()->check() && auth()->user()->currency) {
        return auth()->user()->currency;
    }

    // Check if session has currency
    if (session()->has('currency')) {
        return session('currency');
    }

    // Use locale-based default
    $currencyService = app(\App\Services\CurrencyService::class);
    return $currencyService->getDefaultCurrencyForLocale(app()->getLocale());
}
```

**Add support for AED/SAR in $currencies array (around line 7):**

Current:
```php
protected static $currencies = [
    'IDR' => [...],
    'USD' => [...],
];
```

Add after USD:
```php
'AED' => [
    'symbol' => 'د.إ',
    'name' => 'UAE Dirham',
    'decimal_places' => 2,
    'thousands_separator' => ',',
    'decimal_separator' => '.',
    'locale' => 'ar_AE',
],
'SAR' => [
    'symbol' => 'ر.س',
    'name' => 'Saudi Riyal',
    'decimal_places' => 2,
    'thousands_separator' => ',',
    'decimal_separator' => '.',
    'locale' => 'ar_SA',
],
```

---

### Step 3: Update LocaleController (40 minutes)

**File:** `app/Http/Controllers/LocaleController.php`

**Replace entire `switchLocale()` method (around line 14):**

Current:
```php
public function switchLocale(string $locale)
{
    $supportedLocales = ['en', 'id', 'ar'];
    
    if (in_array($locale, $supportedLocales)) {
        App::setLocale($locale);
        Session::put('locale', $locale);
        
        return redirect()->back()->with('success', __('messages.locale_changed', [], $locale));
    }
    
    return redirect()->back()->with('error', 'Unsupported locale');
}
```

Change to:
```php
public function switchLocale(string $locale)
{
    $supportedLocales = ['en', 'id', 'ar'];
    
    if (in_array($locale, $supportedLocales)) {
        App::setLocale($locale);
        Session::put('locale', $locale);
        
        // NEW: Auto-set currency berdasarkan locale
        $currencyService = app(\App\Services\CurrencyService::class);
        $defaultCurrency = $currencyService->getDefaultCurrencyForLocale($locale);
        
        if (auth()->check()) {
            // Save to database untuk persistence
            auth()->user()->update(['currency' => $defaultCurrency]);
            \Illuminate\Support\Facades\Cache::forget("user_currency_" . auth()->id());
        }
        
        Session::put('currency', $defaultCurrency);
        
        return redirect()->back()
            ->with('success', __('messages.locale_changed', [], $locale))
            ->with('info', 'Currency updated to ' . $defaultCurrency);
    }
    
    return redirect()->back()->with('error', 'Unsupported locale');
}
```

**Update `setCurrency()` method to add validation (around line 31):**

Add validation to ensure currency matches current locale:

```php
public function setCurrency(Request $request)
{
    $currency = $request->input('currency');
    $supported = config('currency.supported_currencies', ['IDR', 'USD']);
    
    if (in_array($currency, $supported)) {
        Session::put('currency', $currency);
        
        // If user is authenticated, save to database
        if (auth()->check()) {
            auth()->user()->update(['currency' => $currency]);
            \Illuminate\Support\Facades\Cache::forget("user_currency_" . auth()->id());
        }
        
        return redirect()->back()
            ->with('success', __('messages.currency_updated'));
    }
    
    return redirect()->back()
        ->with('error', 'Unsupported currency: ' . $currency);
}
```

---

### Step 4: Update LocaleService (25 minutes)

**File:** `app/Services/LocaleService.php`

**Update `getFullSettings()` method (around line 200):**

Current:
```php
public function getFullSettings(?User $user = null): array
{
    return array_merge(
        [
            'locale' => config('app.locale', 'en'),
            'timezone' => config('app.timezone', 'UTC'),
            'currency' => 'USD',
        ],
        $user ? [
            'locale' => $user->locale ?? config('app.locale', 'en'),
            'timezone' => $user->timezone ?? config('app.timezone', 'UTC'),
            'currency' => $user->currency ?? 'USD',
        ] : []
    );
}
```

Change to:
```php
public function getFullSettings(?User $user = null): array
{
    $currencyService = app(\App\Services\CurrencyService::class);
    
    $locale = $user?->locale ?? config('app.locale', 'en');
    $defaultCurrency = $currencyService->getDefaultCurrencyForLocale($locale);
    
    return array_merge(
        [
            'locale' => $locale,
            'timezone' => config('app.timezone', 'UTC'),
            'currency' => $defaultCurrency,
        ],
        $user ? [
            'locale' => $user->locale ?? config('app.locale', 'en'),
            'timezone' => $user->timezone ?? config('app.timezone', 'UTC'),
            'currency' => $user->currency ?? $defaultCurrency,
        ] : []
    );
}
```

---

### Step 5: Update config/currency.php (10 minutes)

**File:** `config/currency.php`

**Update supported_currencies array:**

Current:
```php
'supported_currencies' => [
    'IDR',
    'USD',
],
```

Change to:
```php
'supported_currencies' => [
    'IDR',
    'USD',
    'AED',
    'SAR',
],
```

---

### Step 6: Add Arabic Translations (15 minutes)

**File:** `lang/ar/messages.php`

**Find the currency_option section and update:**

Current (around line 40-42):
```php
'currency_option_idr' => 'Rp IDR',
'currency_option_usd' => '$ USD',
```

Change to:
```php
'currency_option_idr' => 'روبية إندونيسية IDR',
'currency_option_usd' => 'دولار أمريكي USD',
'currency_option_aed' => 'درهم إماراتي AED',
'currency_option_sar' => 'ريال سعودي SAR',
```

---

### Step 7: Update Dashboard Currency Selector (15 minutes)

**File:** `resources/views/dashboard.blade.php`

**Update the currency selector around line 111:**

Current:
```blade
<select name="currency" onchange="this.form.submit()" class="text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
    <option value="IDR" {{ (auth()->user()->currency ?? 'IDR') === 'IDR' ? 'selected' : '' }}>{{ __('messages.currency_option_idr') }}</option>
    <option value="USD" {{ (auth()->user()->currency ?? 'IDR') === 'USD' ? 'selected' : '' }}>{{ __('messages.currency_option_usd') }}</option>
</select>
```

Change to:
```blade
<select name="currency" onchange="this.form.submit()" class="text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
    <option value="IDR" {{ (auth()->user()->currency ?? 'IDR') === 'IDR' ? 'selected' : '' }}>{{ __('messages.currency_option_idr') }}</option>
    <option value="USD" {{ (auth()->user()->currency ?? 'IDR') === 'USD' ? 'selected' : '' }}>{{ __('messages.currency_option_usd') }}</option>
    @if(config('app.locale') === 'ar' || in_array('AED', config('currency.supported_currencies', [])))
        <option value="AED" {{ (auth()->user()->currency ?? 'IDR') === 'AED' ? 'selected' : '' }}>{{ __('messages.currency_option_aed') }}</option>
    @endif
    @if(in_array('SAR', config('currency.supported_currencies', [])))
        <option value="SAR" {{ (auth()->user()->currency ?? 'IDR') === 'SAR' ? 'selected' : '' }}>{{ __('messages.currency_option_sar') }}</option>
    @endif
</select>
```

---

### Step 8: Fix Seller Analytics View (10 minutes)

**File:** `resources/views/seller/analytics/index.blade.php`

**Update around line 249:**

Current:
```blade
return '{{ config("app.currency") === "USD" ? "$" : "Rp " }}' + value.toLocaleString();
```

Change to:
```blade
@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $userCurrency = $currencyService->getUserCurrency(auth()->user());
    $currencySymbol = \App\Helpers\CurrencyHelper::getCurrencyInfo($userCurrency)['symbol'] ?? '$';
@endphp

return '{{ $currencySymbol }}' + value.toLocaleString();
```

---

### Step 9: Create Database Migration (20 minutes)

**File:** `database/migrations/2024_12_12_xxx_add_currency_validation.php`

Create migration file:
```bash
php artisan make:migration add_currency_validation_to_users_table
```

Content:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ensure currency column exists and has sensible default
            if (!Schema::hasColumn('users', 'currency')) {
                $table->string('currency')->default('IDR')->after('timezone');
            } else {
                $table->string('currency')->default('IDR')->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
```

Run migration:
```bash
php artisan migrate
```

---

### Step 10: Create Validation Middleware (30 minutes)

**File:** `app/Http/Middleware/ValidateCurrency.php`

Create new middleware:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\CurrencyService;
use Illuminate\Support\Facades\Log;

class ValidateCurrency
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $currencyService = app(CurrencyService::class);
            $user = auth()->user();
            $currency = $user->currency;
            $supportedCurrencies = $currencyService->getSupportedCurrencies();
            
            // Jika currency tidak valid, reset ke default untuk locale
            if (!in_array($currency, $supportedCurrencies)) {
                Log::warning("Invalid currency detected for user {$user->id}: {$currency}");
                
                $locale = $user->locale;
                $defaultCurrency = $currencyService->getDefaultCurrencyForLocale($locale);
                $user->update(['currency' => $defaultCurrency]);
                
                session()->put('currency', $defaultCurrency);
            }
        }
        
        return $next($request);
    }
}
```

Register in `app/Http/Kernel.php`:
```php
protected $middleware = [
    // ... existing middleware ...
    \App\Http\Middleware\ValidateCurrency::class,
];
```

---

### Step 11: Add Unit Tests (1 hour)

**File:** `tests/Unit/CurrencyServiceTest.php`

Create test file:
```php
<?php

namespace Tests\Unit;

use App\Services\CurrencyService;
use Tests\TestCase;

class CurrencyServiceTest extends TestCase
{
    private CurrencyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CurrencyService::class);
    }

    /** @test */
    public function it_gets_default_currency_for_locale_en()
    {
        $currency = $this->service->getDefaultCurrencyForLocale('en');
        $this->assertEquals('USD', $currency);
    }

    /** @test */
    public function it_gets_default_currency_for_locale_id()
    {
        $currency = $this->service->getDefaultCurrencyForLocale('id');
        $this->assertEquals('IDR', $currency);
    }

    /** @test */
    public function it_gets_default_currency_for_locale_ar()
    {
        $currency = $this->service->getDefaultCurrencyForLocale('ar');
        $this->assertEquals('AED', $currency);
    }

    /** @test */
    public function it_converts_usd_to_idr()
    {
        $result = $this->service->convert(100, 'USD', 'IDR');
        // Should be approximately 1,550,000 (using fallback 15,500)
        $this->assertGreaterThan(1000000, $result);
    }

    /** @test */
    public function it_converts_idr_to_usd()
    {
        $result = $this->service->convert(1550000, 'IDR', 'USD');
        // Should be approximately 100 (using fallback)
        $this->assertGreaterThan(99, $result);
        $this->assertLessThan(101, $result);
    }

    /** @test */
    public function it_returns_same_amount_for_same_currency()
    {
        $result = $this->service->convert(100, 'IDR', 'IDR');
        $this->assertEquals(100, $result);
    }

    /** @test */
    public function it_returns_supported_currencies()
    {
        $currencies = $this->service->getSupportedCurrencies();
        $this->assertContains('IDR', $currencies);
        $this->assertContains('USD', $currencies);
    }
}
```

Run tests:
```bash
php artisan test tests/Unit/CurrencyServiceTest.php
```

---

### Step 12: Add Integration Tests (1 hour)

**File:** `tests/Feature/CurrencyLanguageSyncTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyLanguageSyncTest extends RefreshDatabase
{
    /** @test */
    public function it_syncs_currency_when_switching_language_to_indonesian()
    {
        $user = User::factory()->create(['locale' => 'en', 'currency' => 'USD']);
        
        $this->actingAs($user)
            ->post(route('locale.switch', 'id'));
        
        $user->refresh();
        $this->assertEquals('id', $user->locale);
        $this->assertEquals('IDR', $user->currency);
    }

    /** @test */
    public function it_syncs_currency_when_switching_language_to_english()
    {
        $user = User::factory()->create(['locale' => 'id', 'currency' => 'IDR']);
        
        $this->actingAs($user)
            ->post(route('locale.switch', 'en'));
        
        $user->refresh();
        $this->assertEquals('en', $user->locale);
        $this->assertEquals('USD', $user->currency);
    }

    /** @test */
    public function it_syncs_currency_when_switching_language_to_arabic()
    {
        $user = User::factory()->create(['locale' => 'en', 'currency' => 'USD']);
        
        $this->actingAs($user)
            ->post(route('locale.switch', 'ar'));
        
        $user->refresh();
        $this->assertEquals('ar', $user->locale);
        $this->assertEquals('AED', $user->currency);
    }

    /** @test */
    public function user_can_manually_override_currency()
    {
        $user = User::factory()->create(['locale' => 'id', 'currency' => 'IDR']);
        
        $this->actingAs($user)
            ->post(route('locale.set-currency'), ['currency' => 'USD']);
        
        $user->refresh();
        $this->assertEquals('id', $user->locale);
        $this->assertEquals('USD', $user->currency); // Override works
    }

    /** @test */
    public function wallet_displays_correct_currency_after_language_change()
    {
        $user = User::factory()->create(['locale' => 'en', 'currency' => 'USD']);
        $user->wallet()->create(['balance' => 100, 'currency' => 'IDR']);
        
        $response = $this->actingAs($user)->get(route('wallet.index'));
        $response->assertSee('$'); // USD symbol
        
        // Switch to Indonesian
        $this->post(route('locale.switch', 'id'));
        
        $response = $this->actingAs($user)->get(route('wallet.index'));
        $response->assertSee('Rp'); // IDR symbol
    }
}
```

Run tests:
```bash
php artisan test tests/Feature/CurrencyLanguageSyncTest.php
```

---

## TESTING CHECKLIST

Before committing to production:

### Local Testing
- [ ] All unit tests pass: `php artisan test tests/Unit/CurrencyServiceTest.php`
- [ ] All feature tests pass: `php artisan test tests/Feature/CurrencyLanguageSyncTest.php`
- [ ] Database migration runs: `php artisan migrate`
- [ ] No errors in `php artisan tinker`

### Manual Testing (User Scenarios)
1. **Fresh User Registration**
   - [ ] Register with 'en' locale → currency auto-set to USD
   - [ ] Register with 'id' locale → currency auto-set to IDR
   - [ ] Register with 'ar' locale → currency auto-set to AED

2. **Language Switching**
   - [ ] From EN to ID: currency USD → IDR (auto)
   - [ ] From ID to AR: currency IDR → AED (auto)
   - [ ] From AR to EN: currency AED → USD (auto)

3. **Wallet Operations**
   - [ ] Top-up in USD shows USD amount
   - [ ] Switch to IDR: top-up converted and shows IDR amount
   - [ ] Withdraw minimum shown in correct currency

4. **Transaction History**
   - [ ] Shows transaction in currency at time of transaction
   - [ ] If user switches currency, old transactions still show original currency

5. **Admin Exchange Rates**
   - [ ] Update USD↔IDR rate
   - [ ] Verify new rate used in conversions
   - [ ] Verify fallback still works if DB rate missing

6. **Seller Dashboard**
   - [ ] Analytics show earnings in user's selected currency
   - [ ] Currency symbol correct based on user preference

---

## ROLLBACK PLAN (If Issues)

```bash
# Rollback migration
php artisan migrate:rollback

# Revert code changes via git
git checkout HEAD~1 -- app/Services/CurrencyService.php
git checkout HEAD~1 -- app/Http/Controllers/LocaleController.php
# ... etc for other files

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## DEPLOYMENT STEPS

1. **Backup database:**
   ```bash
   mysqldump -u user -p database > backup_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Pull latest code:**
   ```bash
   git pull origin main
   ```

3. **Install dependencies:**
   ```bash
   composer install
   ```

4. **Run migration:**
   ```bash
   php artisan migrate --force
   ```

5. **Clear caches:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

6. **Run tests:**
   ```bash
   php artisan test
   ```

7. **Monitor logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

---

## SUPPORT & MONITORING

### Error Monitoring
Watch for these error patterns in logs:
```
InvalidExchangeRateException
CurrencyValidationException
UnsupportedLocaleException
UnsupportedCurrencyException
```

### Success Indicators
```
✓ No "Invalid currency detected" warnings
✓ Currency always matches user locale
✓ Exchange rates updated regularly
✓ No failed currency conversions
```

### Troubleshooting

**Issue:** Currency not auto-switching
- Check: Did user have existing currency preference?
- Check: Is DB migration run?
- Fix: Clear user currency via: `User::find($id)->update(['currency' => null])`

**Issue:** Wrong currency symbol showing
- Check: Is CurrencyHelper updated?
- Check: Is config/currency.php updated?
- Fix: Run `php artisan config:clear`

**Issue:** Exchange rate not updating
- Check: Is admin setting new rates in exchange-rates UI?
- Check: Is exchange rate record marked `is_active = true`?
- Check: Run `php artisan cache:clear`

---

**Implementation Date:** [TODAY]  
**Estimated Completion:** [TODAY + 3-5 DAYS]  
**Review Date:** [TODAY + 1 WEEK]

