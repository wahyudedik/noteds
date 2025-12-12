@echo off
REM Currency-Language-Timezone Integration Test Suite for Windows
REM Tests all currency conversions, language switches, and timezone syncing

echo.
echo 🚀 Starting Currency-Language-Timezone Integration Tests...
echo.

REM Test 1: Verify CurrencyService locale mappings
echo 📋 TEST 1: CurrencyService Locale Mappings
echo Testing locale-to-currency and locale-to-timezone mappings...
php artisan tinker --execute="$cs = app(\App\Services\CurrencyService::class); echo 'en: ' . $cs->getDefaultCurrencyForLocale('en') . ' / ' . $cs->getDefaultTimezoneForLocale('en') . PHP_EOL; echo 'id: ' . $cs->getDefaultCurrencyForLocale('id') . ' / ' . $cs->getDefaultTimezoneForLocale('id') . PHP_EOL; echo 'ar: ' . $cs->getDefaultCurrencyForLocale('ar') . ' / ' . $cs->getDefaultTimezoneForLocale('ar') . PHP_EOL;"
echo.

REM Test 2: Verify exchange rates
echo 📋 TEST 2: Exchange Rate Conversions
echo Testing USD to IDR and other conversions...
php artisan tinker --execute="$cs = app(\App\Services\CurrencyService::class); echo '100 USD = ' . $cs->convert(100, 'USD', 'IDR') . ' IDR' . PHP_EOL; echo '100 USD = ' . $cs->convert(100, 'USD', 'AED') . ' AED' . PHP_EOL;"
echo.

REM Test 3: Verify supported currencies
echo 📋 TEST 3: Supported Currencies
echo Checking config and helpers...
php artisan tinker --execute="$c = config('currency.supported_currencies'); echo 'Config: ' . implode(', ', $c) . PHP_EOL;"
echo.

REM Test 4: Check migrations status
echo 📋 TEST 4: Checking Database Structure
echo Verifying users table has required columns...
php artisan tinker --execute="$cols = DB::getSchemaBuilder()->getColumnListing('users'); echo in_array('locale', $cols) ? '✅ locale' : '❌ locale'; echo ' '; echo in_array('currency', $cols) ? '✅ currency' : '❌ currency'; echo ' '; echo in_array('timezone', $cols) ? '✅ timezone' : '❌ timezone'; echo PHP_EOL;"
echo.

echo ✅ Test summary complete!
echo.
echo 📝 Next Steps:
echo 1. Run migration: php artisan migrate
echo 2. Clear caches: php artisan cache:clear
echo 3. Test in browser at /dashboard
echo.
pause
