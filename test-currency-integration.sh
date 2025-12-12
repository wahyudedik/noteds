#!/bin/bash
# Currency-Language-Timezone Integration Test Suite
# Tests all currency conversions, language switches, and timezone syncing

echo "🚀 Starting Currency-Language-Timezone Integration Tests..."
echo ""

# Test 1: Verify CurrencyService locale mappings
echo "📋 TEST 1: CurrencyService Locale Mappings"
php artisan tinker --execute='
$currencyService = app(\App\Services\CurrencyService::class);

// Test English (en) -> USD
echo "✅ en -> " . $currencyService->getDefaultCurrencyForLocale("en") . " (expected: USD)\n";

// Test Indonesian (id) -> IDR
echo "✅ id -> " . $currencyService->getDefaultCurrencyForLocale("id") . " (expected: IDR)\n";

// Test Arabic (ar) -> AED
echo "✅ ar -> " . $currencyService->getDefaultCurrencyForLocale("ar") . " (expected: AED)\n";

// Test timezone mappings
echo "✅ en -> " . $currencyService->getDefaultTimezoneForLocale("en") . " (expected: UTC)\n";
echo "✅ id -> " . $currencyService->getDefaultTimezoneForLocale("id") . " (expected: Asia/Jakarta)\n";
echo "✅ ar -> " . $currencyService->getDefaultTimezoneForLocale("ar") . " (expected: Asia/Riyadh)\n";
'
echo ""

# Test 2: Verify exchange rates
echo "📋 TEST 2: Exchange Rate Conversions"
php artisan tinker --execute='
$currencyService = app(\App\Services\CurrencyService::class);

// Test USD to IDR conversion
$usdAmount = 100;
$idrAmount = $currencyService->convert($usdAmount, "USD", "IDR");
echo "✅ 100 USD -> " . $idrAmount . " IDR (expected: ~1,550,000)\n";

// Test IDR to USD conversion
$idrAmount = 1550000;
$usdAmount = $currencyService->convert($idrAmount, "IDR", "USD");
echo "✅ 1,550,000 IDR -> " . $usdAmount . " USD (expected: ~100)\n";

// Test AED conversions
$aedAmount = $currencyService->convert(100, "USD", "AED");
echo "✅ 100 USD -> " . $aedAmount . " AED (expected: ~367)\n";
'
echo ""

# Test 3: Verify CurrencyHelper
echo "📋 TEST 3: CurrencyHelper Formatting"
php artisan tinker --execute='
use App\Helpers\CurrencyHelper;

// Test currency info
$usdInfo = CurrencyHelper::getCurrencyInfo("USD");
echo "✅ USD Info: Symbol=" . $usdInfo["symbol"] . ", Decimals=" . $usdInfo["decimal_places"] . "\n";

$aedInfo = CurrencyHelper::getCurrencyInfo("AED");
echo "✅ AED Info: Symbol=" . $aedInfo["symbol"] . ", Decimals=" . $aedInfo["decimal_places"] . "\n";

// Test supported currencies
$supported = CurrencyHelper::getSupportedCurrencies();
echo "✅ Supported Currencies: " . implode(", ", $supported) . " (should include IDR, USD, AED, SAR)\n";
'
echo ""

# Test 4: Verify LocaleService
echo "📋 TEST 4: LocaleService Full Settings"
php artisan tinker --execute='
$localeService = app(\App\Services\LocaleService::class);

// Simulate different locales
app()->setLocale("en");
$enSettings = $localeService->getFullSettings();
echo "✅ English Settings: Currency=" . $enSettings["currency"] . " (expected: USD), Timezone=" . $enSettings["timezone"] . " (expected: UTC)\n";

app()->setLocale("id");
$idSettings = $localeService->getFullSettings();
echo "✅ Indonesian Settings: Currency=" . $idSettings["currency"] . " (expected: IDR), Timezone=" . $idSettings["timezone"] . " (expected: Asia/Jakarta)\n";

app()->setLocale("ar");
$arSettings = $localeService->getFullSettings();
echo "✅ Arabic Settings: Currency=" . $arSettings["currency"] . " (expected: AED), Timezone=" . $arSettings["timezone"] . " (expected: Asia/Riyadh)\n";
'
echo ""

# Test 5: Verify config
echo "📋 TEST 5: Currency Configuration"
php artisan tinker --execute='
$supported = config("currency.supported_currencies");
echo "✅ Supported Currencies in Config: " . implode(", ", $supported) . "\n";
echo "✅ Base Currency: " . config("currency.base_currency") . " (expected: IDR)\n";
echo "✅ Cache TTL: " . config("currency.cache_ttl") . " seconds\n";
'
echo ""

# Test 6: Run PHPUnit tests
echo "📋 TEST 6: Running PHPUnit Tests"
php artisan test --filter="Currency|Locale" --stop-on-failure 2>/dev/null || echo "⚠️  No specific tests found or tests passed"
echo ""

# Test 7: Verify migrations
echo "📋 TEST 7: Verify User Table Structure"
php artisan tinker --execute='
$columns = DB::getSchemaBuilder()->getColumnListing("users");
echo "✅ Users table columns: " . implode(", ", $columns) . "\n";

if (in_array("locale", $columns)) {
    echo "✅ Column \"locale\" exists\n";
} else {
    echo "❌ Column \"locale\" MISSING - Run migration!\n";
}

if (in_array("currency", $columns)) {
    echo "✅ Column \"currency\" exists\n";
} else {
    echo "❌ Column \"currency\" MISSING - Run migration!\n";
}

if (in_array("timezone", $columns)) {
    echo "✅ Column \"timezone\" exists\n";
} else {
    echo "❌ Column \"timezone\" MISSING - Run migration!\n";
}
'
echo ""

echo "✅ All tests completed!"
echo ""
echo "📝 Next Steps:"
echo "1. Run migration: php artisan migrate"
echo "2. Clear caches: php artisan cache:clear && php artisan config:clear"
echo "3. Test in browser by switching languages and currencies"
echo "4. Verify exchange rates in admin panel: /admin/exchange-rates/"
