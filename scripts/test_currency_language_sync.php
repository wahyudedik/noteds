<?php

/**
 * Currency-Language Sync Test Script
 * 
 * This script tests that currency automatically syncs with language selection
 * Run: php scripts/test_currency_language_sync.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\CurrencyService;
use App\Helpers\CurrencyHelper;

echo "\n========================================\n";
echo "   Currency-Language Sync Test\n";
echo "========================================\n\n";

$currencyService = app(CurrencyService::class);

// Test 1: Language to Currency Mapping
echo "✓ Test 1: Language → Currency Mapping\n";
echo str_repeat("-", 40) . "\n";

$locales = ['en', 'id', 'ar'];
foreach ($locales as $locale) {
    $currency = $currencyService->getDefaultCurrencyForLocale($locale);
    echo "  $locale → $currency\n";
}
echo "\n";

// Test 2: Expected Mappings
echo "✓ Test 2: Verify Expected Mappings\n";
echo str_repeat("-", 40) . "\n";

$expected = [
    'en' => 'USD',
    'id' => 'IDR',
    'ar' => 'AED',
];

$passed = 0;
$failed = 0;

foreach ($expected as $locale => $expectedCurrency) {
    $actualCurrency = $currencyService->getDefaultCurrencyForLocale($locale);
    if ($actualCurrency === $expectedCurrency) {
        echo "  ✅ $locale → $actualCurrency (PASS)\n";
        $passed++;
    } else {
        echo "  ❌ $locale → $actualCurrency, expected $expectedCurrency (FAIL)\n";
        $failed++;
    }
}
echo "\n";

// Test 3: Currency Formatting
echo "✓ Test 3: Currency Formatting\n";
echo str_repeat("-", 40) . "\n";

$testAmount = 10000;
$currencies = ['IDR', 'USD', 'AED', 'SAR'];

foreach ($currencies as $currency) {
    $formatted = CurrencyHelper::format($testAmount, $currency, 'IDR');
    echo "  $currency: $formatted\n";
}
echo "\n";

// Test 4: Exchange Rate Fallbacks
echo "✓ Test 4: Exchange Rate Fallbacks\n";
echo str_repeat("-", 40) . "\n";

$conversions = [
    ['from' => 'USD', 'to' => 'IDR'],
    ['from' => 'USD', 'to' => 'AED'],
    ['from' => 'USD', 'to' => 'SAR'],
    ['from' => 'IDR', 'to' => 'USD'],
    ['from' => 'AED', 'to' => 'USD'],
    ['from' => 'SAR', 'to' => 'USD'],
];

foreach ($conversions as $conv) {
    $rate = $currencyService->getExchangeRate($conv['from'], $conv['to']);
    echo sprintf("  %s → %s: %s\n", $conv['from'], $conv['to'], number_format($rate, 4));
}
echo "\n";

// Test 5: Supported Currencies
echo "✓ Test 5: Supported Currencies\n";
echo str_repeat("-", 40) . "\n";

$supported = config('currency.supported_currencies', []);
echo "  " . implode(', ', $supported) . "\n\n";

// Test 6: Currency Info
echo "✓ Test 6: Currency Info\n";
echo str_repeat("-", 40) . "\n";

foreach ($currencies as $currency) {
    $info = CurrencyHelper::getCurrencyInfo($currency);
    if ($info) {
        echo sprintf(
            "  %s: %s (%s)\n",
            $currency,
            $info['name'] ?? 'Unknown',
            $info['symbol'] ?? '?'
        );
    }
}
echo "\n";

// Summary
echo "========================================\n";
echo "   Test Summary\n";
echo "========================================\n";
echo "  ✅ Passed: $passed\n";
echo "  ❌ Failed: $failed\n";
echo "  📊 Total:  " . ($passed + $failed) . "\n";
echo "\n";

if ($failed === 0) {
    echo "🎉 All tests passed! Currency-Language sync is working correctly.\n\n";
    exit(0);
} else {
    echo "⚠️  Some tests failed. Please review the implementation.\n\n";
    exit(1);
}
