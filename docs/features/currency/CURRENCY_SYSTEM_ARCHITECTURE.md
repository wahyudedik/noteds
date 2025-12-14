# Currency & Language System Architecture

## Current System Flow (BEFORE FIX)

```
┌─────────────────────────────────────────────────────────────────┐
│                    USER CHANGES LANGUAGE                        │
│                                                                 │
│  Dashboard → Select Language → Click Indonesian                │
└─────────────────────────┬───────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│              LocaleController::switchLocale()                   │
│                                                                 │
│  1. Set app locale to 'id'                                     │
│  2. Save to session                                            │
│  3. Redirect back                                              │
│                                                                 │
│  ❌ MISSING: Update user's currency!                           │
└─────────────────────────┬───────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│               LocaleService::setUserLocale()                    │
│                                                                 │
│  1. Validate locale 'id'                                       │
│  2. Update user.locale = 'id'                                  │
│  3. Clear cache                                                │
│                                                                 │
│  ❌ MISSING: Update user's currency!                           │
└─────────────────────────┬───────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│                 USER'S CURRENCY STATE                           │
│                                                                 │
│  Before:  locale='en', currency='USD'                          │
│  After:   locale='id', currency='USD'  ❌ STILL USD!           │
│                                                                 │
│  Expected: locale='id', currency='IDR'                         │
└─────────────────────────┬───────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│              WHEN USER VISITS WALLET PAGE                       │
│                                                                 │
│  CurrencyHelper::getDefaultCurrency() returns:                 │
│                                                                 │
│  1. Check user.currency? → USD (from DB) ✓                    │
│  2. Use USD, not IDR                                           │
│                                                                 │
│  Result: USD symbol shows even though locale is 'id' ❌       │
└─────────────────────────────────────────────────────────────────┘

PROBLEM: Inconsistent state - locale='id' but currency='USD'
```

---

## Improved System Flow (AFTER FIX)

```
┌─────────────────────────────────────────────────────────────────┐
│                    USER CHANGES LANGUAGE                        │
│                                                                 │
│  Dashboard → Select Language → Click Indonesian                │
└─────────────────────────┬───────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│         NEW: LocaleController::switchLocale()                   │
│                                                                 │
│  1. Set app locale to 'id'                                     │
│  2. Save to session                                            │
│                                                                 │
│  ✅ NEW: Get default currency for locale 'id'                 │
│  ✅ NEW: Update user.currency = 'IDR'                         │
│  ✅ NEW: Save to session                                       │
└─────────────────────────┬───────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│           NEW: CurrencyService::getDefaultCurrencyForLocale()   │
│                                                                 │
│  Get locale 'id' → Return 'IDR'                                │
│  Get locale 'en' → Return 'USD'                                │
│  Get locale 'ar' → Return 'AED'                                │
│                                                                 │
│  ✅ Mapping is consistent and automatic                         │
└─────────────────────────┬───────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│                 USER'S CURRENCY STATE                           │
│                                                                 │
│  Before:  locale='en', currency='USD'                          │
│  After:   locale='id', currency='IDR'  ✅ CORRECT!            │
│                                                                 │
│  Consistent: locale and currency match!                        │
└─────────────────────────┬───────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│              WHEN USER VISITS WALLET PAGE                       │
│                                                                 │
│  CurrencyHelper::getDefaultCurrency() returns:                 │
│                                                                 │
│  1. Check user.currency? → IDR (from DB) ✅                   │
│  2. Use IDR, matches locale 'id'                               │
│                                                                 │
│  Result: IDR symbol (Rp) shows - CONSISTENT! ✅              │
└─────────────────────────────────────────────────────────────────┘

SUCCESS: Consistent state - locale='id' AND currency='IDR'
```

---

## Currency-Locale Mapping

### Before
```
Locale → Currency Mapping

en → USD ✅
id → IDR ✅
ar → USD ❌ (Should be AED!)
```

### After
```
Locale → Currency Mapping

en → USD ✅
id → IDR ✅
ar → AED ✅ (Fixed! Now uses Arabic currency)
```

---

## Data Flow in Wallet Page

```
┌──────────────────────────────────────────────────────────────┐
│                   WALLET PAGE REQUEST                        │
└──────────────────────────────────┬───────────────────────────┘
                                   │
                        ┌──────────┴──────────┐
                        │                     │
                        ▼                     ▼
        ┌────────────────────────┐   ┌──────────────────┐
        │   User Object          │   │   Session        │
        │  {                     │   │  {               │
        │   locale: 'id'         │   │   locale: 'id'   │
        │   currency: 'IDR'      │   │   currency: 'IDR'│
        │  }                     │   │  }               │
        └────────────┬───────────┘   └────────┬─────────┘
                     │                        │
                     └────────────┬───────────┘
                                  │
                                  ▼
                    ┌───────────────────────────┐
                    │ CurrencyService           │
                    │ getUserCurrency(user)     │
                    │                           │
                    │ Return: 'IDR'             │
                    └────────────┬──────────────┘
                                 │
                                 ▼
                    ┌───────────────────────────┐
                    │ CurrencyHelper            │
                    │ getCurrencyInfo('IDR')    │
                    │                           │
                    │ Return: {                 │
                    │   symbol: 'Rp'            │
                    │   decimal_places: 0       │
                    │   ...                     │
                    │ }                         │
                    └────────────┬──────────────┘
                                 │
                                 ▼
                    ┌───────────────────────────┐
                    │ View Renders              │
                    │                           │
                    │ Balance: Rp 50.000.000    │
                    │ (Correct format!)         │
                    └───────────────────────────┘
```

---

## Exchange Rate System

```
┌────────────────────────────────────────────────────────────┐
│                    TRANSACTION FLOW                        │
│                                                            │
│  User wants to convert: 100 USD → ? IDR                  │
└─────────────────────────┬─────────────────────────────────┘
                          │
                          ▼
        ┌─────────────────────────────────┐
        │ CurrencyService::convert()      │
        │ convert(100, 'USD', 'IDR')     │
        └─────────────────┬───────────────┘
                          │
                ┌─────────┴──────────┐
                │                    │
                ▼                    ▼
    ┌──────────────────┐   ┌──────────────────┐
    │ Try DB First     │   │ If DB Empty      │
    │                  │   │ Use Fallback     │
    │ ExchangeRate     │   │ Rate             │
    │ where from='USD' │   │                  │
    │ where to='IDR'   │   │ fallbacks = {    │
    │ where active=1   │   │   USD→IDR: 15500│
    │                  │   │ }                │
    │ If found: ✅    │   │                  │
    │ rate = 15,500    │   │ If not found: ❌│
    │                  │   │ Use fallback     │
    └──────────┬───────┘   └────────┬─────────┘
               │                    │
               └────────┬───────────┘
                        │
                        ▼
        ┌─────────────────────────────────┐
        │ Calculate Result                │
        │ 100 × 15,500 = 1,550,000       │
        │                                 │
        │ Round based on currency:        │
        │ IDR = 0 decimals                │
        │ Final: 1,550,000 IDR            │
        └─────────────────────────────────┘
```

**Security Considerations:**
- ✅ Always lock rate at transaction time
- ✅ Audit log all conversions
- ✅ Validate rate is reasonable (not 0.00001 or 999999)
- ✅ Alert if using fallback rate (DB might be outdated)

---

## Three Views of Currency System

### 1. Admin Perspective
```
Admin Dashboard
    ↓
Exchange Rate Management (/admin/exchange-rates/)
    ↓
Create/Edit Rate: 1 USD = 15,500 IDR
    ↓
Save to Database
    ↓
CurrencyService reads from DB
    ↓
All conversions use latest rate
```

### 2. User Perspective
```
User Settings
    ↓
Dashboard Language Selector
    ↓
Select: Indonesian
    ↓
Automatic: Currency changes to IDR
    ↓
Wallet Page
    ↓
Balance shows in IDR (Rp)
    ↓
All transactions in IDR
```

### 3. Developer Perspective
```
LocaleController::switchLocale('id')
    ↓
Set app locale + session
    ↓
NEW: Call CurrencyService::getDefaultCurrencyForLocale('id')
    ↓
NEW: Update user.currency = 'IDR'
    ↓
All subsequent views use correct currency
    ↓
CurrencyHelper::getDefaultCurrency() returns 'IDR'
    ↓
Templates render with Rp symbol
```

---

## Key Components & Dependencies

```
┌─────────────────────────────────────┐
│        LocaleController             │
│  (Route handler for language)       │
│                                     │
│  ├─ switchLocale()    ◄─ UPDATE    │
│  ├─ setCurrency()     ◄─ FIX       │
│  └─ setTimezone()                  │
└────────────────┬────────────────────┘
                 │ uses
                 ▼
┌─────────────────────────────────────┐
│      CurrencyService                │
│  (Business logic for currency)      │
│                                     │
│  ├─ getBaseCurrency()               │
│  ├─ getSupportedCurrencies()        │
│  ├─ getUserCurrency()               │
│  ├─ convert()                       │
│  ├─ format()                        │
│  ├─ getRate()                       │
│  └─ NEW: getDefaultCurrencyForLocale() │
└────────────┬──────────────────────────┘
             │ uses
             ▼
┌─────────────────────────────────────┐
│      CurrencyHelper                 │
│  (View helpers & formatting)        │
│                                     │
│  ├─ format()                        │
│  ├─ getDefaultCurrency()            │
│  ├─ getCurrencyInfo()               │
│  └─ convert()                       │
└────────────┬──────────────────────────┘
             │ uses
             ▼
┌─────────────────────────────────────┐
│    Database Models                  │
│                                     │
│  ├─ User (locale, currency, tz)    │
│  ├─ ExchangeRate (rates DB)        │
│  └─ Wallet (balance in IDR)        │
└─────────────────────────────────────┘
```

---

## Before & After Comparison

### Scenario: User registers in Indonesian

| Step | Before | After |
|------|--------|-------|
| 1. Register | locale='id', currency='USD' ❌ | locale='id', currency='IDR' ✅ |
| 2. Visit wallet | Shows $ USD ❌ | Shows Rp IDR ✅ |
| 3. Switch to English | locale='en', currency='USD' ✅ | locale='en', currency='USD' ✅ |
| 4. Back to Indonesian | locale='id', currency='USD' ❌ | locale='id', currency='IDR' ✅ |

### Scenario: User switches from English to Arabic

| Step | Before | After |
|------|--------|-------|
| 1. Start | locale='en', currency='USD' ✅ | locale='en', currency='USD' ✅ |
| 2. Switch to Arabic | locale='ar', currency='USD' ❌ | locale='ar', currency='AED' ✅ |
| 3. Visit wallet | Shows $ USD (confusing!) ❌ | Shows د.إ AED (correct!) ✅ |
| 4. Transaction | Amount in USD (foreign!) ❌ | Amount in AED (local!) ✅ |

---

## Implementation Complexity

```
┌─────────────────────────────────────────┐
│  IMPLEMENTATION COMPLEXITY MATRIX        │
└─────────────────────────────────────────┘

File Changes:    ████░░░░░░  (4/10) Easy
Database:        ██░░░░░░░░  (1/10) Simple
Testing:         ███░░░░░░░  (2/10) Medium
Risk Level:      ░░░░░░░░░░  (0/10) LOW - Backward compatible
Time Required:   ████░░░░░░  (3-5 days total)

Total Effort: LOW (easy changes, good test coverage, no breaking changes)
```

---

## Validation Rules

```
┌──────────────────────────────────┐
│  Validation Layer                │
└──────────────────────────────────┘

Input: user.locale = 'id'
  ├─ Supported? (en, id, ar) → Yes ✅
  ├─ Exists in config? → Yes ✅
  └─ Action: Set user.locale = 'id'
             Get default currency for 'id'
             Update user.currency = 'IDR'

Input: user.currency = 'IDR'
  ├─ Supported? (IDR, USD, AED, SAR) → Yes ✅
  ├─ Matches locale 'id'? → Yes ✅
  └─ Action: ALLOW - consistent state ✅

Input: user.currency = 'USD' but user.locale = 'id'
  ├─ Supported? → Yes ✅
  ├─ Matches locale 'id'? → No ❌
  └─ Action: Allow (user override)
             Log warning if auto-sync mode
             Show info message
```

---

## Transaction Safety Example

```
┌─────────────────────────────────────┐
│   TRANSACTION WITH RATE LOCK        │
└─────────────────────────────────────┘

1. User initiates transfer: 100 USD → IDR
   
   Time: 2024-12-12 10:15:30
   Rate at this moment: 1 USD = 15,550 IDR
   
2. System creates transaction record:
   {
     from_amount: 100,
     from_currency: 'USD',
     to_currency: 'IDR',
     exchange_rate_used: 15550,  ◄─ LOCKED!
     exchange_rate_timestamp: '2024-12-12 10:15:30',
     calculated_amount: 1555000,
     to_amount: 1555000
   }

3. Later (next day):
   Exchange rate changes: 1 USD = 15,600 IDR
   
   But: Transaction still shows 1,555,000 IDR
       Because we locked rate at transaction time ✅
       
   User can see:
   - Original amount: 100 USD
   - Rate used: 15,550 (when I made transaction)
   - Received: 1,555,000 IDR
   - Audit: When & what rate was used

Result: SAFE, TRANSPARENT, AUDITABLE ✅
```

---

## Final Checklist for Implementation

```
Phase 1: Code Changes
  ☐ Update CurrencyService (add method)
  ☐ Update CurrencyHelper (fix locale mapping)
  ☐ Update LocaleController (add auto-sync)
  ☐ Update LocaleService (fix getFullSettings)
  ☐ Update config/currency.php (add AED/SAR)
  ☐ Update lang/ar/messages.php (add translations)
  ☐ Update dashboard currency selector
  ☐ Update seller analytics view
  ☐ Add validation middleware
  ☐ Create database migration

Phase 2: Testing
  ☐ Unit tests (CurrencyService methods)
  ☐ Feature tests (locale switching)
  ☐ Integration tests (full wallet flow)
  ☐ Manual testing (all 3 locales)

Phase 3: Deployment
  ☐ Backup database
  ☐ Run migration
  ☐ Clear caches
  ☐ Run full test suite
  ☐ Monitor logs for 24h
  ☐ Update documentation
```

---

**Document Version:** 1.0  
**Last Updated:** December 12, 2025  
**Status:** Audit Complete - Ready for Implementation

