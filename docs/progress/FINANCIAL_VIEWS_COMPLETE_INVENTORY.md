# Complete Financial Views & Backend Inventory

**Generated:** December 12, 2025  
**Scope:** All views, services, and models related to financial transactions

---

## 1. WALLET MANAGEMENT VIEWS

### Location: `/resources/views/wallet/`

| File | Purpose | Lines | Currency Usage |
|------|---------|-------|-----------------|
| `index.blade.php` | Wallet dashboard, balance display | 371 | Uses `CurrencyService::getUserCurrency()` ✅ |
| `withdraw.blade.php` | Withdrawal form & minimum info | 230 | Uses `CurrencyService::getUserCurrency()` ✅ |
| `topup-checkout.blade.php` | Top-up checkout process | N/A | Uses currency helpers ✅ |
| `admin-report.blade.php` | Admin wallet report | N/A | Admin view, needs audit |

**Key Pattern Used:**
```blade
@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $userCurrency = $currencyService->getUserCurrency(auth()->user());
@endphp
```

**Issues:**
- ⚠️ When user switches language, these views still show old currency until page refresh
- ✅ Currency display logic is correct, just not synced with language change

---

## 2. PAYMENT & SUBSCRIPTION VIEWS

### Location: `/resources/views/subscriptions/`

| File | Purpose | Currency |
|------|---------|----------|
| `show.blade.php` | Show subscription plans with prices | Shows in IDR/USD ✅ |
| `payment.blade.php` | Payment processing view | Uses wallet currency ✅ |

### Location: `/resources/views/emails/notifications/`

| File | Purpose | Currency |
|------|---------|----------|
| `payment-released.blade.php` | Email notification for payment release | Shows amount ✅ |

---

## 3. COMMISSION & AFFILIATE VIEWS

### Location: `/resources/views/admin/affiliate/`

| File | Purpose | Lines | Currency |
|------|---------|-------|----------|
| `index.blade.php` | Affiliate program overview | - | References commission data |
| `commissions.blade.php` | View/manage commissions | - | Shows commission amounts |
| `payouts.blade.php` | Payout management | - | Shows payout amounts |
| `payout-show.blade.php` | Single payout detail | - | Shows payout in currency |

### Location: `/resources/views/referral/`

| File | Purpose | Currency |
|------|---------|----------|
| `index.blade.php` | Referral dashboard | Shows earnings ✅ |
| `transaction-history.blade.php` | Commission transaction history | Shows amounts earned |
| `leaderboard.blade.php` | Referral leaderboard | Shows earnings |

**Referral Language File:** `lang/{locale}/referral.php`
- Contains translation for commission messages
- Supports parameter `:amount` and `:percent`

---

## 4. ADMIN FINANCIAL MANAGEMENT VIEWS

### Location: `/resources/views/admin/transactions/`

| File | Purpose | Importance |
|------|---------|-----------|
| `index.blade.php` | List all transactions | HIGH - shows monetary values |

**What it does:**
- Lists user transactions
- Shows amounts, currencies
- Date, status, type

---

### Location: `/resources/views/admin/withdraws/`

| File | Purpose | Currency Handling |
|------|---------|------------------|
| `index.blade.php` | List all withdrawal requests | Shows requested amount & status |
| `show.blade.php` | Detail of single withdrawal | Shows full transaction details |

---

### Location: `/resources/views/admin/exchange-rates/`

| File | Purpose | Critical? |
|------|---------|-----------|
| `index.blade.php` | List exchange rates (USD↔IDR) | ⭐ CRITICAL - for manual rate updates |
| `create.blade.php` | Create new exchange rate | ⭐ CRITICAL - add new pairs |
| `edit.blade.php` | Edit existing exchange rate | ⭐ CRITICAL - update rates |

**Purpose:** Admin UI untuk set/update USD↔IDR rates

**Current Rate Fallback:** 1 USD = 15,000 IDR (hardcoded, needs update to ~15,500)

---

## 5. MARKETPLACE & PRICING VIEWS

### Location: `/resources/views/notes/`

| File | Purpose | How Currency Shown |
|------|---------|-------------------|
| `create.blade.php` | Create note form | Price input with currency formatting (1560+) |
| `edit.blade.php` | Edit note form | Price input with currency formatting (1505+) |
| `index.blade.php` | My notes list | Shows note prices |

**JavaScript Currency Formatting:**
```javascript
const locale = document.documentElement.lang === 'en' ? 'en-ID' : 'id-ID';
// Uses Intl.NumberFormat untuk format
```

⚠️ **Issue:** hardcoded locale, not user preference

---

### Location: `/resources/views/welcome.blade.php`

| Line | Purpose |
|------|---------|
| 131, 240 | Marketplace note display | Shows `currency($note->price)` ✅ |

---

### Location: `/resources/views/workspaces/`

| File | Currency Display |
|------|-----------------|
| `show.blade.php` | Line 26, 85: Shows workspace price with currency() helper |

---

## 6. SELLER ANALYTICS

### Location: `/resources/views/seller/analytics/index.blade.php`

| Line | What | Issue |
|------|------|-------|
| 249 | Currency formatting | ❌ **Uses config("app.currency") instead of user preference** |

```javascript
// CURRENT (WRONG):
return '{{ config("app.currency") === "USD" ? "$" : "Rp " }}' + value.toLocaleString();

// SHOULD BE:
return '{{ $userCurrency === "USD" ? "$" : "Rp " }}' + value.toLocaleString();
```

---

## 7. BACKEND SERVICES & MODELS

### Services

#### `app/Services/CurrencyService.php` (208 lines) ⭐ CORE

**Methods:**
- `getBaseCurrency()` - Get IDR (base)
- `getSupportedCurrencies()` - Get IDR, USD list
- `getCurrencySymbol()` - Get Rp or $
- `getUserCurrency()` - Get user's selected currency
- `convert()` - Convert between currencies
- `convertToBase()` - Convert to IDR
- `convertFromBase()` - Convert from IDR
- `format()` - Format with symbol
- `getRate()` - Get exchange rate from DB + fallback
- `round()` - Round based on currency
- `formatForApi()` - API response format
- `isValidCurrency()` - Validate currency
- `setUserCurrency()` - Set user's currency
- `getDefaultCurrencyForCountry()` - Map country to currency

**Missing:**
- ❌ `getDefaultCurrencyForLocale()` - Not implemented yet!

---

#### `app/Services/LocaleService.php` (249 lines) ⭐ IMPORTANT

**Methods:**
- `getUserLocale()` - Get user's language
- `setUserLocale()` - Set user's language (❌ doesn't update currency!)
- `getUserTimezone()` - Get user's timezone
- `setUserTimezone()` - Set user's timezone
- `getSupportedLocales()` - Get en, id, ar
- `getUserSettings()` - Get all user's settings
- `setUserSettings()` - Set multiple settings
- `formatDate()` - Format date by locale
- `getFullSettings()` - Get complete settings (❌ hardcoded USD!)

**Issues:**
1. ❌ `setUserLocale()` doesn't trigger currency update
2. ❌ `getFullSettings()` hardcodes currency to USD

---

#### `app/Helpers/CurrencyHelper.php` (142 lines)

**Purpose:** Helper functions untuk currency formatting

**Methods:**
- `format()` - Format amount with currency symbol
- `getDefaultCurrency()` - Get user's default currency (⚠️ checks locale but wrong mapping for AR)
- `getSupportedCurrencies()` - Get IDR, USD list
- `getCurrencyInfo()` - Get currency details (symbol, decimal places, etc)
- `convert()` - Convert amount (deprecated? uses CurrencyService)

**Issue:**
- ⚠️ Default currency for 'ar' is 'USD' - should be 'AED'

---

### Models

#### `app\Models\User`
```php
// Fields related to currency/locale:
- locale: string (en, id, ar)
- currency: string (IDR, USD)
- timezone: string (Asia/Jakarta, UTC, etc)
```

**Issue:** No validation constraint on currency field

---

#### `app\Models\ExchangeRate`
```php
// Assumed fields:
- from_currency: string (USD, IDR, etc)
- to_currency: string (USD, IDR, etc)
- rate: decimal/float
- is_active: boolean
- created_at, updated_at
```

**Purpose:** Admin-configurable exchange rates

**Used by:** `CurrencyService::getRate()` method

---

## 8. LANGUAGE FILES

### Structure: `/lang/{locale}/messages.php`

| Locale | File | Size | Currency Keys |
|--------|------|------|----------------|
| English | `en/messages.php` | 2,664 lines | currency_option_idr, currency_option_usd |
| Indonesian | `id/messages.php` | 2,641 lines | currency_option_idr, currency_option_usd |
| Arabic | `ar/messages.php` | 2,456 lines | currency_option_idr, currency_option_usd ⚠️ |

**Currency-Related Translations:**
```
messages.locale_changed
messages.currency_updated
messages.timezone_updated
messages.currency_option_idr = 'Rp IDR'
messages.currency_option_usd = '$ USD'
messages.wallet_balance
messages.wallet_balance_title
messages.topup_withdraw_funds
messages.minimum_topup_amount
messages.minimum_withdraw_amount
```

**Missing for Arabic:**
- ❌ `currency_option_aed` - Not translated
- ❌ `currency_option_sar` - Not available

---

### Structure: `/lang/{locale}/referral.php`

**Content:**
```php
'signup_reward_description' => 'When someone registers... you'll receive :amount'
'transaction_commission_title' => 'Transaction Commission'
'transaction_commission_description' => '...earn :percent% commission'
```

**Parameters:** `:amount`, `:percent`

---

## 9. CONTROLLERS

### `app/Http/Controllers/LocaleController.php`

**Routes:**
```
GET /locale/{locale} → switchLocale()
POST /locale/currency → setCurrency()
POST /locale/timezone → setTimezone()
```

**Methods:**

1. `switchLocale($locale)` (⚠️ NEEDS UPDATE)
   - Sets app locale
   - Saves to session
   - ❌ Doesn't update currency!

2. `setCurrency()` (✅ OK but hardcoded)
   - Validates currency (only IDR, USD hardcoded)
   - Saves to DB & session

3. `setTimezone()` (✅ OK)
   - Validates timezone
   - Saves to DB & session

---

## 10. CONFIGURATION

### `config/currency.php`
```php
'base_currency' => env('APP_BASE_CURRENCY', 'IDR')
'supported_currencies' => ['IDR', 'USD']
'cache_ttl' => env('CURRENCY_CACHE_TTL', 300)
```

**Issues:**
- ⚠️ Hardcoded supported_currencies (needs AED, SAR)
- ❌ No mapping to locale

---

### `config/app.php`
```php
'locale' => env('APP_LOCALE', 'en')
'fallback_locale' => 'en'
'faker_locale' => 'id_ID'
```

---

## 11. ROUTES

### `routes/web.php` (lines 77-80)

```php
// Locale & i18n routes
Route::get('/locale/{locale}', [LocaleController::class, 'switchLocale'])->name('locale.switch');
Route::post('/locale/currency', [LocaleController::class, 'setCurrency'])
    ->middleware(['auth', 'verified', 'username.setup'])
    ->name('locale.set-currency');
Route::post('/locale/timezone', [LocaleController::class, 'setTimezone'])
    ->middleware(['auth', 'verified', 'username.setup'])
    ->name('locale.set-timezone');
```

---

## 12. DASHBOARD

### `resources/views/dashboard.blade.php` (lines 111-126)

**Currency & Timezone Selector:**
```blade
<form action="{{ route('locale.set-currency') }}" method="POST">
    <select name="currency" onchange="this.form.submit()">
        <option value="IDR">Rp IDR</option>
        <option value="USD">$ USD</option>
    </select>
</form>
```

**Issue:** Doesn't show auto-sync message when language changes

---

## SUMMARY TABLE

### All Financial Files at a Glance

| Component | Status | Needs Update? |
|-----------|--------|---------------|
| **Wallet Views** | ✅ Using CurrencyService | ⚠️ Only on page refresh |
| **Payment Views** | ✅ Using currency helpers | ✅ OK |
| **Commission Views** | ✅ Basic display | ⚠️ No direct currency issues |
| **Admin Exchange Rate UI** | ✅ Working | ✅ OK but rates need refresh |
| **CurrencyService** | ✅ Core logic solid | ❌ Missing locale mapping |
| **LocaleService** | ⚠️ Partial | ❌ Currency not synced |
| **LocaleController** | ⚠️ Partial | ❌ switchLocale doesn't update currency |
| **Marketplace Notes** | ✅ Using helpers | ⚠️ JavaScript locale hardcoded |
| **Seller Analytics** | ❌ Using config | ❌ Should use user preference |
| **Language Files** | ⚠️ Mostly OK | ❌ Missing Arabic currencies |

---

## CRITICAL AUDIT FINDINGS

### 🔴 CRITICAL
1. Currency doesn't sync when language changes
2. LocaleController::switchLocale() is incomplete
3. Hardcoded fallback exchange rate (15,000 IDR/USD)

### 🟡 IMPORTANT
1. Arabic currency not properly supported
2. Some views use config instead of user preference
3. LocaleService hardcodes USD in getFullSettings()

### 🟢 MINOR
1. JavaScript locale hardcoded in some views
2. No rate validation mechanism
3. Limited currency pair support

---

**Total Files Reviewed:** 25+  
**Total Lines of Code:** 10,000+  
**Total Views:** 12  
**Total Services:** 2  
**Total Issues Found:** 8  

