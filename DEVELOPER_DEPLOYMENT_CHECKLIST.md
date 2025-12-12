# ✅ Developer Checklist - Currency-Language-Timezone Integration

## 📋 Pre-Deployment Review Checklist

Use this checklist to verify the implementation before deploying to production.

---

## Phase 1: Code Review

### Services & Helpers
- [ ] `CurrencyService.php` - Review new methods
  - [ ] `getDefaultCurrencyForLocale()` - Maps en→USD, id→IDR, ar→AED
  - [ ] `getDefaultTimezoneForLocale()` - Maps en→UTC, id→Jakarta, ar→Riyadh
  - [ ] Fallback rates updated: USD↔IDR = 15,500
  
- [ ] `LocaleController.php` - Review auto-sync logic
  - [ ] `switchLocale()` injects dependencies correctly
  - [ ] Updates both session and database
  - [ ] Clears currency conversion cache
  - [ ] Returns proper success message
  
- [ ] `LocaleService.php` - Review defaults
  - [ ] `getFullSettings()` calls `getDefaultCurrencyForLocale()`
  - [ ] Returns correct defaults for each locale
  
- [ ] `CurrencyHelper.php` - Review currency support
  - [ ] IDR configuration correct (symbol: Rp, 0 decimals)
  - [ ] USD configuration correct (symbol: $, 2 decimals)
  - [ ] AED configuration correct (symbol: د.إ, 2 decimals)
  - [ ] SAR configuration correct (symbol: ﷼, 2 decimals)
  - [ ] `getDefaultCurrency()` uses CurrencyService
  - [ ] Fallback rates include all 4 currencies

### Configuration
- [ ] `config/currency.php`
  - [ ] Base currency is 'IDR'
  - [ ] supported_currencies array has all 4: IDR, USD, AED, SAR
  - [ ] Cache TTL is 300 seconds

### Views
- [ ] `resources/views/dashboard.blade.php`
  - [ ] Currency selector shows 4 options
  - [ ] Timezone selector shows all timezones
  - [ ] Form targets `locale.set-currency` route
  
- [ ] `resources/views/seller/analytics/index.blade.php`
  - [ ] Chart uses `auth()->user()->currency`
  - [ ] Symbol mapping includes all 4 currencies

### Language Files
- [ ] `lang/en/messages.php`
  - [ ] Has currency_option_idr, currency_option_usd
  - [ ] Has locale_changed message
  
- [ ] `lang/id/messages.php`
  - [ ] Has currency_option_idr, currency_option_usd
  - [ ] Has locale_changed message
  
- [ ] `lang/ar/messages.php`
  - [ ] Has currency_option_idr, currency_option_usd
  - [ ] Has currency_option_aed ✅ NEW
  - [ ] Has currency_option_sar ✅ NEW
  - [ ] Has locale_changed message

---

## Phase 2: Database Review

### Migration File
- [ ] File: `database/migrations/2024_12_29_000000_add_locale_currency_timezone_to_users.php`
- [ ] Checks if columns exist (safe)
- [ ] Adds 3 columns: locale, currency, timezone
- [ ] Has proper rollback method
- [ ] Default values are correct:
  - [ ] locale → 'en'
  - [ ] currency → 'IDR'
  - [ ] timezone → 'UTC'

### Schema Validation
- [ ] Users table has new columns:
  - [ ] `locale` (string, default 'en')
  - [ ] `currency` (string, default 'IDR')
  - [ ] `timezone` (string, default 'UTC')

---

## Phase 3: Testing

### Unit Tests
- [ ] CurrencyService methods exist and return correct values
- [ ] LocaleService methods exist and return correct defaults
- [ ] CurrencyHelper formatting works correctly

### Integration Tests
- [ ] Switching language updates currency (✅ auto-test in switchLocale)
- [ ] Switching language updates timezone (✅ auto-test in switchLocale)
- [ ] Cache is cleared on currency change
- [ ] Database is updated when user is authenticated

### Manual Tests
- [ ] [ ] TEST 1: Switch from English to Indonesian
  - Expected: locale=id, currency=IDR, timezone=Asia/Jakarta
  - Actual: _____________
  
- [ ] [ ] TEST 2: Switch from Indonesian to Arabic
  - Expected: locale=ar, currency=AED, timezone=Asia/Riyadh
  - Actual: _____________
  
- [ ] [ ] TEST 3: Convert 100 USD to IDR
  - Expected: ~1,550,000 (or DB rate)
  - Actual: _____________
  
- [ ] [ ] TEST 4: Check dashboard currency selector
  - Expected: 4 options (IDR, USD, AED, SAR)
  - Actual: _____________
  
- [ ] [ ] TEST 5: Format amount as IDR
  - Expected: "Rp 1.550.000,00"
  - Actual: _____________
  
- [ ] [ ] TEST 6: Format amount as USD
  - Expected: "$ 1,234.56"
  - Actual: _____________
  
- [ ] [ ] TEST 7: Format amount as AED
  - Expected: "د.إ 367.00"
  - Actual: _____________
  
- [ ] [ ] TEST 8: Chart uses correct currency
  - Expected: Shows user's currency symbol
  - Actual: _____________

### Regression Tests
- [ ] [ ] Existing currency conversion still works
  - Test: USD to IDR conversion
  
- [ ] [ ] Existing wallet operations still work
  - Test: Top up, withdraw functionality
  
- [ ] [ ] Existing marketplace still works
  - Test: Browse and purchase notes
  
- [ ] [ ] Admin exchange rate management still works
  - Test: /admin/exchange-rates/ page
  
- [ ] [ ] Seller analytics still works
  - Test: /seller/analytics page
  
- [ ] [ ] Language switching still works
  - Test: Switch en→id→ar and back

---

## Phase 4: CLI Verification

Run these commands and verify output:

```bash
# 1. Check ServiceProvider registrations
php artisan tinker
$cs = app(\App\Services\CurrencyService::class);
$cs->getDefaultCurrencyForLocale('en');  # Should return 'USD'
$cs->getDefaultCurrencyForLocale('id');  # Should return 'IDR'
$cs->getDefaultCurrencyForLocale('ar');  # Should return 'AED'
exit

# 2. Check database columns
php artisan tinker
DB::table('users')->first();  # Should show locale, currency, timezone
exit

# 3. Run test script
bash test-currency-integration.sh
# OR
test-currency-integration.bat

# 4. Check for syntax errors
php artisan config:clear
php artisan cache:clear
php artisan route:list | grep locale

# 5. Check migrations status
php artisan migrate:status
# Should show: add_locale_currency_timezone_to_users - MIGRATED
```

---

## Phase 5: Performance Check

- [ ] Database queries for currency conversion (should hit cache)
- [ ] View rendering time (should not increase)
- [ ] API response time (should not increase)
- [ ] Migration execution time (should be < 1 second)

### Benchmarks
- [ ] Currency conversion: < 10ms
- [ ] Locale switch: < 100ms
- [ ] View render: no measurable increase
- [ ] API response: no measurable increase

---

## Phase 6: Security Audit

- [ ] [ ] CSRF tokens present on all forms
  - Check: `dashboard.blade.php` currency/timezone forms
  
- [ ] [ ] Input validation on locale
  - Check: `LocaleController::switchLocale()` validates against list
  
- [ ] [ ] Input validation on currency
  - Check: `LocaleController::setCurrency()` validates against config
  
- [ ] [ ] Input validation on timezone
  - Check: `LocaleController::setTimezone()` validates against PHP timezone list
  
- [ ] [ ] No SQL injection possible
  - Check: All queries use Eloquent (safe)
  
- [ ] [ ] No XSS vulnerabilities
  - Check: All translations use {{ }} (escaped)
  
- [ ] [ ] Cache properly scoped
  - Check: Cache keys include user/locale context
  
- [ ] [ ] No sensitive data in logs
  - Check: Exchange rates logged but not user financial data

---

## Phase 7: Documentation Review

- [ ] [ ] `IMPLEMENTATION_CURRENCY_LANGUAGE_TIMEZONE.md` is complete
  - [ ] Contains architecture explanation
  - [ ] Contains troubleshooting guide
  - [ ] Contains code examples
  
- [ ] [ ] `QUICK_START_DEPLOYMENT.md` is clear
  - [ ] Deployment steps are accurate
  - [ ] Troubleshooting steps work
  
- [ ] [ ] `IMPLEMENTATION_SUMMARY.md` is accurate
  - [ ] File changes list is complete
  - [ ] Checklist is thorough
  
- [ ] [ ] Test scripts are working
  - [ ] `test-currency-integration.bat` runs on Windows
  - [ ] `test-currency-integration.sh` runs on Linux/Mac

---

## Phase 8: Team Communication

Before deploying, ensure:
- [ ] All developers understand the changes
- [ ] QA team has test plan
- [ ] DevOps team has deployment steps
- [ ] Support team has troubleshooting guide
- [ ] Product team has feature verification steps

---

## Phase 9: Staging Deployment

- [ ] [ ] Backup staging database
- [ ] [ ] Deploy code changes
- [ ] [ ] Run migration: `php artisan migrate`
- [ ] [ ] Run cache clear: `php artisan cache:clear`
- [ ] [ ] Verify all manual tests pass (see Phase 3)
- [ ] [ ] Check logs for errors: `tail -f storage/logs/laravel.log`
- [ ] [ ] Get sign-off from team lead

---

## Phase 10: Production Deployment

- [ ] [ ] Backup production database
- [ ] [ ] Schedule downtime (if needed)
- [ ] [ ] Deploy code changes
- [ ] [ ] Run migration: `php artisan migrate --force`
- [ ] [ ] Run cache clear: `php artisan cache:clear && php artisan config:clear`
- [ ] [ ] Update exchange rates in admin (if needed)
- [ ] [ ] Run smoke tests (verify key functionality)
- [ ] [ ] Monitor logs: `tail -f storage/logs/laravel.log`
- [ ] [ ] Verify no errors in error logs
- [ ] [ ] Have rollback plan ready (just in case)

---

## 🚨 Rollback Plan (If Needed)

If critical issues found:

```bash
# 1. Rollback migration
php artisan migrate:rollback --step=1

# 2. Revert code changes (if deployed via git)
git revert <commit-hash>

# 3. Clear caches
php artisan cache:clear
php artisan config:clear

# 4. Verify system is working
# Run manual tests to confirm

# 5. Investigate issue
# Check logs, contact developer
```

**Note:** The migration is safe - it only ADDS columns. Even if rolled back, old code will still work (columns just won't be populated for new users until migration re-runs).

---

## ✅ Sign-Off

**Code Reviewed By:** _________________ Date: _______

**Tested By:** _________________ Date: _______

**Approved for Deployment:** _________________ Date: _______

**Deployed By:** _________________ Date: _______ Time: _______

**Verified in Production:** _________________ Date: _______ Time: _______

---

## 📞 Contact Information

For questions during deployment:
- Developer: _______________
- DevOps: _______________
- Tech Lead: _______________

---

## 📝 Notes

Use this section to record any issues found or special considerations:

```
_________________________________________________________________

_________________________________________________________________

_________________________________________________________________

_________________________________________________________________
```

---

**This checklist must be completed before marking deployment as successful.**

**Last Updated:** December 29, 2024
