# Currency & Language Integration Audit Report

**Date:** December 12, 2025  
**Status:** Comprehensive Audit Complete  
**Security Level:** Critical - Financial Operations

---

## Executive Summary

Sistem keuangan Noteds saat ini memiliki infrastruktur yang baik untuk mengelola pertukaran mata uang (USD ↔ IDR), namun **MASIH BELUM ADA INTEGRASI OTOMATIS ANTARA PEMILIHAN BAHASA DAN MATA UANG**. Ini adalah risiko yang perlu diperbaiki untuk memastikan transaksi yang aman dan konsisten.

---

## 1. SISTEM YANG SUDAH ADA

### 1.1 Infrastruktur Mata Uang (✅ Sudah Baik)

#### File: `config/currency.php`
```
Base Currency: IDR (default untuk internal accounting)
Supported: IDR, USD
Cache TTL: 300 detik
```

#### File: `app/Services/CurrencyService.php` (208 lines)
**Capabilities:**
- ✅ `getBaseCurrency()` - Mendapat currency base (IDR)
- ✅ `getUserCurrency($user)` - Ambil currency user (dari DB, session, atau default)
- ✅ `convert()` - Konversi antar mata uang
- ✅ `convertToBase()` - Konversi ke IDR
- ✅ `convertFromBase()` - Konversi dari IDR
- ✅ `format()` - Format display currency dengan symbol
- ✅ `formatForApi()` - Format untuk API response
- ✅ `getRate()` - Ambil exchange rate dari DB dengan fallback hardcoded

**Fallback Rates (Fallback saat DB tidak ada rate):**
```php
'USD' => ['IDR' => 15000],   // 1 USD = 15000 IDR (HARDCODED - PERLU UPDATE)
'IDR' => ['USD' => 1/15000], // 1 IDR = 1/15000 USD
```

#### File: `app/Helpers/CurrencyHelper.php` (142 lines)
**Supported Currencies:**
- IDR: Rp, 0 decimal places (e.g., Rp 100.000)
- USD: $, 2 decimal places (e.g., $100.00)

**Key Method: `getDefaultCurrency()`**
```php
public static function getDefaultCurrency(): string
{
    // Check user DB preference
    if (auth()->check() && auth()->user()->currency) {
        return auth()->user()->currency;
    }

    // Check session
    if (session()->has('currency')) {
        return session('currency');
    }

    // DEFAULT BASED ON LOCALE (⚠️ INCOMPLETE - MISSING ARABIC MAPPING)
    return match ($locale) {
        'id' => 'IDR',
        'ar' => 'USD',    // ❌ HARUS DIUBAH KE AED/SAR
        'en' => 'USD',
        default => 'IDR',
    };
}
```

### 1.2 Infrastruktur Lokalisasi (✅ Sudah Ada)

#### File: `app/Services/LocaleService.php` (249 lines)
**Supported Locales:**
```php
'en' => ['name' => 'English', 'flag' => '🇺🇸'],
'id' => ['name' => 'Indonesian', 'flag' => '🇮🇩'],
'ar' => ['name' => 'Arabic', 'flag' => '🇸🇦'],
```

**Key Methods:**
- ✅ `getUserLocale($user)` - Ambil locale user (default: 'en')
- ✅ `setUserLocale($user, $locale)` - Set locale user
- ✅ `getFullSettings($user)` - Ambil semua settings (include currency hardcoded USD)

**❌ PROBLEM:** Method `getFullSettings()` hardcoded currency ke USD, tidak mengikuti locale!
```php
public function getFullSettings(?User $user = null): array
{
    return array_merge(
        [...],
        $user ? [
            'locale' => $user->locale ?? config('app.locale', 'en'),
            'timezone' => $user->timezone ?? config('app.timezone', 'UTC'),
            'currency' => $user->currency ?? 'USD',  // ❌ HARDCODED USD
        ] : []
    );
}
```

#### File: `app/Http/Controllers/LocaleController.php`
**Routes:**
- `GET /locale/{locale}` → `switchLocale()` - Switch bahasa (TIDAK auto-switch currency)
- `POST /locale/currency` → `setCurrency()` - Set mata uang manual
- `POST /locale/timezone` → `setTimezone()` - Set timezone

**❌ PROBLEM:** `switchLocale()` TIDAK otomatis mengubah mata uang!
```php
public function switchLocale(string $locale)
{
    // ... hanya set locale, TIDAK set currency
    Session::put('locale', $locale);
}
```

### 1.3 Language Files (Translation)

#### Structure: `/lang/{locale}/messages.php` (2,456 - 2,664 lines per file)

**Currency-related translations yang ada:**
```
messages.php:
  - currency_option_idr = 'Rp IDR'
  - currency_option_usd = '$ USD'
  - currency_updated = 'Mata uang berhasil diperbarui'
  - wallet_balance_title = 'Saldo Dompet'
  - topup_withdraw_funds = 'Isi saldo atau tarik dana'
  - minimum_topup_amount = 'Minimal top-up adalah :amount'
  - minimum_withdraw_info = 'Informasi minimum withdraw: :amount'

referral.php:
  - transaction_commission_title
  - transaction_commission_description (with :percent parameter)
```

**❌ MISSING:** Tidak ada translation untuk SAR/AED untuk Arabic locale!

---

## 2. VIEWS BERKAITAN KEUANGAN (FINANCIAL VIEWS)

### 2.1 Wallet Management
✅ `/resources/views/wallet/`
- `index.blade.php` - Dashboard dompet (371 lines)
- `withdraw.blade.php` - Form penarikan dana (230 lines)
- `topup-checkout.blade.php` - Proses top-up
- `admin-report.blade.php` - Admin report

**How they use currency:**
```blade
@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $userCurrency = $currencyService->getUserCurrency(auth()->user());
    $walletBalance = currency($wallet->balance, $userCurrency, $walletCurrency);
@endphp
```

✅ **Good:** Menggunakan CurrencyService untuk get user's currency preference

❌ **Problem:** Ketika user switch language, currency TIDAK ikut berubah

### 2.2 Payment & Subscription
- `/resources/views/subscriptions/payment.blade.php`
- `/resources/views/subscriptions/show.blade.php` (menampilkan harga)
- `/resources/views/emails/notifications/payment-released.blade.php`

### 2.3 Commission & Affiliate
- `/resources/views/admin/affiliate/commissions.blade.php`
- `/resources/views/admin/affiliate/payouts.blade.php`
- `/resources/views/referral/transaction-history.blade.php` (menampilkan earning)

### 2.4 Admin Financial Management
- `/resources/views/admin/transactions/index.blade.php`
- `/resources/views/admin/withdraws/index.blade.php`
- `/resources/views/admin/exchange-rates/` (create, edit, index)
  - **Ini untuk admin set exchange rates USD ↔ IDR**

### 2.5 Seller Analytics
- `/resources/views/seller/analytics/index.blade.php` (line 249)
```blade
return '{{ config("app.currency") === "USD" ? "$" : "Rp " }}' + value.toLocaleString();
// ❌ Menggunakan config, BUKAN user's preference
```

### 2.6 Notes/Marketplace
- `/resources/views/notes/create.blade.php` (line 1560+)
  - Format price dengan `baseCurrency`
- `/resources/views/notes/edit.blade.php` (line 1505+)
  - Menampilkan price in user's currency
- `/resources/views/welcome.blade.php` (line 131, 240)
  - Marketplace price display

---

## 3. CRITICAL FINDINGS

### 🔴 ISSUE #1: Currency TIDAK Auto-Switch Saat Bahasa Diubah
**Severity:** HIGH  
**Impact:** User confusion & potential transaction errors

**Scenario:**
1. User dalam locale `id` (Indonesian) dengan currency `IDR`
2. User ubah bahasa ke `en` (English)
3. **Expected:** Currency otomatis jadi `USD`
4. **Actual:** Currency tetap `IDR` ❌

**Root Cause:**
- `LocaleController::switchLocale()` TIDAK trigger currency change
- CurrencyHelper hanya check user DB & session, tidak read user's locale saat itu

---

### 🔴 ISSUE #2: Arabic Currency Mapping TIDAK Ada
**Severity:** MEDIUM  
**Impact:** Konfusi untuk user Arabic-speaking

**Current Mapping:**
```
'en' => 'USD'   ✅
'id' => 'IDR'   ✅
'ar' => 'USD'   ❌ SALAH! Harus AED/SAR
```

**Real World:**
- Negara Arab kebanyakan pakai: **SAR** (Saudi Riyal) atau **AED** (UAE Dirham)
- Memakai USD untuk Arabic locale tidak praktis untuk user di Arab countries

---

### 🔴 ISSUE #3: Hardcoded USD Fallback Rate (15,000 IDR per USD)
**Severity:** HIGH  
**Impact:** Transaksi tidak akurat jika admin belum setup exchange rates

**Current Fallback:**
```php
'USD' => ['IDR' => 15000],  // HARDCODED dari 2023?
```

**Real Current Rate:** ~15,500 - 16,000 IDR per USD (Dec 2024)

**Risk:** Jika database exchange rate corrupt/kosong, semua transaksi pakai rate lama!

---

### 🔴 ISSUE #4: LocaleService::getFullSettings() Hardcoded USD
**Severity:** MEDIUM  
**Impact:** Default settings tidak sesuai locale

```php
'currency' => $user->currency ?? 'USD',  // Should depend on locale!
```

Should be:
```php
'currency' => $user->currency ?? $this->getDefaultCurrencyForLocale($user->locale),
```

---

### 🟡 ISSUE #5: Some Views Use config("app.currency") Instead of User Preference
**Severity:** LOW-MEDIUM  
**Impact:** Admin/seller analytics show wrong currency

**Example:** `/seller/analytics/index.blade.php` line 249
```blade
{{ config("app.currency") === "USD" ? "$" : "Rp " }}
// Should use: $currencyService->getUserCurrency()
```

---

### 🟡 ISSUE #6: Missing Arabic Currency Translation
**Severity:** LOW  
**Impact:** Arabic users see English currency names

**Missing:** `lang/ar/messages.php` perlu ada terjemahan untuk:
- `currency_option_sar` = 'ر.س SAR' (Saudi Riyal)
- `currency_option_aed` = 'د.إ AED' (UAE Dirham)
- `currency_option_idr` = 'روبية إندونيسية IDR'

---

## 4. DATABASE & MODELS

### Model: `App\Models\User`
**Fields:**
- `currency` - VARCHAR/STRING (IDR atau USD)
- `locale` - VARCHAR/STRING (en, id, ar)
- `timezone` - VARCHAR/STRING

**Missing:** TIDAK ada constraint atau default value berdasarkan locale!

### Model: `App\Models\ExchangeRate`
**Purpose:** Admin-configurable exchange rates

**Fields (assumed):**
- `from_currency` (USD, IDR, etc)
- `to_currency` (USD, IDR, etc)
- `rate` (DECIMAL)
- `is_active` (BOOLEAN)
- `updated_at` (untuk verify freshness)

**✅ Good:** Bisa di-update admin via admin UI

---

## 5. IMPLEMENTATION PLAN (Recommended)

### Phase 1: Auto Currency Mapping (HIGH PRIORITY)

#### 1.1 Update CurrencyService
Add new method:
```php
public function getDefaultCurrencyForLocale(?string $locale = null): string
{
    $locale = $locale ?? app()->getLocale();
    
    return match ($locale) {
        'en' => 'USD',
        'id' => 'IDR',
        'ar' => 'AED',  // Changed from USD to AED
        default => $this->baseCurrency,
    };
}
```

#### 1.2 Update LocaleController::switchLocale()
```php
public function switchLocale(string $locale)
{
    $supportedLocales = ['en', 'id', 'ar'];
    
    if (in_array($locale, $supportedLocales)) {
        App::setLocale($locale);
        Session::put('locale', $locale);
        
        // NEW: Auto-set currency berdasarkan locale
        if (auth()->check()) {
            $defaultCurrency = app(CurrencyService::class)
                ->getDefaultCurrencyForLocale($locale);
            auth()->user()->update(['currency' => $defaultCurrency]);
            Session::put('currency', $defaultCurrency);
        }
        
        return redirect()->back()
            ->with('success', __('messages.locale_changed', [], $locale))
            ->with('info', 'Currency updated to ' . $defaultCurrency);
    }
    
    return redirect()->back()->with('error', 'Unsupported locale');
}
```

#### 1.3 Update CurrencyHelper::getDefaultCurrency()
```php
public static function getDefaultCurrency(): string
{
    if (auth()->check() && auth()->user()->currency) {
        return auth()->user()->currency;
    }

    if (session()->has('currency')) {
        return session('currency');
    }

    // NEW: Use current app locale
    $currencyService = app(CurrencyService::class);
    return $currencyService->getDefaultCurrencyForLocale(app()->getLocale());
}
```

#### 1.4 Update LocaleService::getFullSettings()
```php
public function getFullSettings(?User $user = null): array
{
    $locale = $user?->locale ?? config('app.locale', 'en');
    $currencyService = app(CurrencyService::class);
    
    return array_merge(
        [
            'locale' => $locale,
            'timezone' => config('app.timezone', 'UTC'),
            'currency' => $currencyService->getDefaultCurrencyForLocale($locale),
        ],
        $user ? [
            'locale' => $user->locale ?? config('app.locale', 'en'),
            'timezone' => $user->timezone ?? config('app.timezone', 'UTC'),
            'currency' => $user->currency 
                ?? $currencyService->getDefaultCurrencyForLocale($user->locale),
        ] : []
    );
}
```

---

### Phase 2: Expand Supported Currencies for Arabic (MEDIUM PRIORITY)

#### 2.1 Update config/currency.php
```php
'supported_currencies' => [
    'IDR',
    'USD',
    'AED',  // NEW: UAE Dirham
    'SAR',  // NEW: Saudi Riyal
],
```

#### 2.2 Update CurrencyHelper::$currencies
```php
protected static $currencies = [
    // ... existing ...
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
];
```

#### 2.3 Add Conversion Rates to CurrencyService
Update getRate() fallback:
```php
$fallbacks = [
    'USD' => ['IDR' => 15500, 'AED' => 3.67, 'SAR' => 3.75],
    'IDR' => ['USD' => 1/15500, 'AED' => 1/4200, 'SAR' => 1/4140],
    'AED' => ['USD' => 1/3.67, 'IDR' => 4200, 'SAR' => 1.02],
    'SAR' => ['USD' => 1/3.75, 'IDR' => 4140, 'AED' => 0.98],
];
```

#### 2.4 Add Arabic Translations to lang/ar/messages.php
```php
'currency_option_idr' => 'روبية إندونيسية IDR',
'currency_option_usd' => 'دولار أمريكي USD',
'currency_option_aed' => 'درهم إماراتي AED',
'currency_option_sar' => 'ريال سعودي SAR',
```

---

### Phase 3: Security & Validation (HIGH PRIORITY)

#### 3.1 Add Middleware untuk Verify Currency
```php
// app/Http/Middleware/ValidateCurrency.php
public function handle(Request $request, Closure $next)
{
    if (auth()->check()) {
        $currency = auth()->user()->currency;
        $supportedCurrencies = app(CurrencyService::class)->getSupportedCurrencies();
        
        if (!in_array($currency, $supportedCurrencies)) {
            // Reset ke default untuk locale
            $locale = auth()->user()->locale;
            $defaultCurrency = app(CurrencyService::class)
                ->getDefaultCurrencyForLocale($locale);
            auth()->user()->update(['currency' => $defaultCurrency]);
        }
    }
    
    return $next($request);
}
```

#### 3.2 Add Database Validation
Migration untuk memastikan valid currency:
```php
// database/migrations/xxxx_add_currency_validation.php
Schema::table('users', function (Blueprint $table) {
    $table->string('currency')
        ->change()
        ->default('IDR');
    
    // Add constraint jika DB support
    // CONSTRAINT check_valid_currency CHECK (currency IN ('IDR', 'USD', 'AED', 'SAR'))
});
```

#### 3.3 Update Form Validation
```php
'currency' => 'required|in:' . implode(',', $currencyService->getSupportedCurrencies()),
'locale' => 'required|in:en,id,ar',
```

---

### Phase 4: Fix Views (MEDIUM PRIORITY)

#### 4.1 Fix seller/analytics/index.blade.php
```blade
@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $userCurrency = $currencyService->getUserCurrency(auth()->user());
@endphp

// In JavaScript:
return new Intl.NumberFormat('{{ app()->getLocale() }}', {
    style: 'currency',
    currency: '{{ $userCurrency }}'
}).format(value);
```

#### 4.2 Add Currency Display Consistency
Create helper blade component:
```blade
@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $userCurrency = $currencyService->getUserCurrency();
@endphp

<!-- Use across all views -->
<x-currency-display :amount="$value" :currency="$userCurrency" />
```

---

### Phase 5: Update Exchange Rate Fallback (MEDIUM PRIORITY)

#### 5.1 Create Command untuk Update Fallback Rates
```php
// app/Console/Commands/UpdateFallbackExchangeRates.php

// Fetch dari external API (seperti exchangerate-api.com)
// Update fallback rates dalam code atau .env
```

#### 5.2 Create Admin Dashboard untuk Exchange Rates
- Sudah ada di `/admin/exchange-rates/`
- **Ensure** rates di-update regularly

#### 5.3 Add Warning Jika Fallback Rates Outdated
```php
public function getRate(string $from, string $to): float
{
    // ... try DB first ...
    
    // Fallback logic
    if ($fallbackRate) {
        \Log::warning("Using fallback rate for {$from}-{$to}: fallback might be outdated!");
        return $fallbackRate;
    }
}
```

---

## 6. TRANSACTION SAFETY MEASURES

### 6.1 Rate Lock Mechanism
```php
// Setiap transaksi lock rate at time of transaction
class Transaction extends Model
{
    // Baru fields:
    protected $fillable = [
        'amount',
        'currency',
        'exchange_rate_used',  // Lock rate saat transaksi
        'exchange_rate_timestamp', // Kapan rate ini diambil
        'base_amount', // Jumlah dalam base currency
    ];
}
```

### 6.2 Audit Trail
```php
// Log setiap currency conversion
\Log::info("Currency conversion", [
    'from' => $from,
    'to' => $to,
    'rate' => $rate,
    'amount' => $amount,
    'user_id' => auth()->id(),
]);
```

### 6.3 Rate Validation
```php
public function convert(float $amount, string $from, string $to): float
{
    $rate = $this->getRate($from, $to);
    
    // Validasi rate tidak terlalu gila
    if ($rate < 0.001 || $rate > 100000) {
        throw new InvalidExchangeRateException(
            "Suspicious rate: {$from} -> {$to} = {$rate}"
        );
    }
    
    return $this->round($amount * $rate, $to);
}
```

---

## 7. TESTING CHECKLIST

### 7.1 Unit Tests
```
✅ CurrencyService::convert()
✅ CurrencyService::getDefaultCurrencyForLocale()
✅ CurrencyHelper::getDefaultCurrency()
✅ LocaleService::setUserLocale() triggers currency update
✅ LocaleController::switchLocale() triggers currency update
✅ Invalid currency rejected
✅ Rate validation works
```

### 7.2 Integration Tests
```
✅ User switch locale 'id' -> currency 'IDR'
✅ User switch locale 'en' -> currency 'USD'
✅ User switch locale 'ar' -> currency 'AED' (or SAR)
✅ Manual currency override still works
✅ Wallet balance display in correct currency
✅ Withdraw minimum shows correct currency
✅ Commission calculation use correct rate
✅ Exchange rate fallback works if DB down
```

### 7.3 Manual Testing
```
User Journey:
1. Register -> default locale 'en' -> currency 'USD'
2. Go to dashboard -> currency selector shows 'USD'
3. Switch to Indonesian -> currency auto-change to 'IDR'
4. Check wallet balance -> shows in IDR with correct formatting
5. Do a transaction -> amount locked in current rate
6. Switch back to English -> currency auto-change to 'USD'
7. Check transaction history -> shows original IDR with USD conversion at time of transaction
```

---

## 8. RECOMMENDED FILE CHANGES

| File | Type | Priority | Change |
|------|------|----------|--------|
| app/Services/CurrencyService.php | Modify | HIGH | Add getDefaultCurrencyForLocale() |
| app/Http/Controllers/LocaleController.php | Modify | HIGH | Update switchLocale() to auto-set currency |
| app/Services/LocaleService.php | Modify | MEDIUM | Update getFullSettings() |
| app/Helpers/CurrencyHelper.php | Modify | HIGH | Update getDefaultCurrency() logic |
| config/currency.php | Modify | MEDIUM | Add AED, SAR support |
| lang/ar/messages.php | Modify | MEDIUM | Add currency translations |
| resources/views/seller/analytics/index.blade.php | Modify | MEDIUM | Use user currency not config |
| database/migrations/ | Create | HIGH | Add currency validation |
| app/Http/Middleware/ValidateCurrency.php | Create | HIGH | Currency validation middleware |
| tests/Unit/CurrencyServiceTest.php | Create | HIGH | Add comprehensive tests |

---

## 9. MIGRATION PATH (No Breaking Changes)

### Step 1: Add New Methods (Backward Compatible)
- Add `getDefaultCurrencyForLocale()` to CurrencyService ✅

### Step 2: Update LocaleController (Transparent to Users)
- Modify `switchLocale()` to auto-set currency ✅

### Step 3: Database Migration
- Add validation constraint untuk currency field ✅

### Step 4: Update Views Gradually
- Update high-traffic views first ✅
- Test thoroughly before deploying ✅

### Step 5: Monitoring
- Log all currency conversions ✅
- Monitor fallback rate usage ✅
- Alert if rates are suspicious ✅

---

## 10. DEPLOYMENT CHECKLIST

Before going to production:

- [ ] All tests pass (unit, integration, e2e)
- [ ] Database migration run successfully
- [ ] Exchange rates are up-to-date in admin panel
- [ ] Fallback rates verified with current market rates
- [ ] User notification sent about currency auto-switching feature
- [ ] Support team trained on new currency handling
- [ ] Monitoring/alerts configured
- [ ] Rollback plan documented

---

## 11. CONCLUSION

**Overall Security Assessment:** ⚠️ **MEDIUM RISK**

**Strengths:**
- ✅ Solid CurrencyService infrastructure
- ✅ Database-driven exchange rates (admin configurable)
- ✅ Format helpers untuk berbagai mata uang
- ✅ Basic fallback mechanism

**Weaknesses:**
- ❌ NO automatic currency sync with language
- ❌ Hardcoded fallback rates (potentially outdated)
- ❌ Arabic currency not properly supported
- ❌ Some views don't use proper currency source
- ❌ No rate validation mechanism

**Immediate Actions Required:**
1. Implement currency auto-sync with language (HIGH PRIORITY)
2. Update fallback exchange rates to current market rates
3. Add Arabic currency support (AED/SAR)
4. Add rate validation & warnings
5. Create comprehensive tests

**Timeline to Fix:** 3-5 days (for careful implementation + testing)

---

**Report Generated By:** AI Code Audit System  
**Next Review:** After implementation phase complete

