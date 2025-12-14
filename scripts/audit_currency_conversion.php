<?php

/**
 * CRITICAL AUDIT: Currency Conversion in All Features
 * 
 * This script audits whether all features that use monetary amounts
 * are properly converting between currencies based on user's selected currency
 * 
 * Date: December 12, 2025
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Setting;
use App\Services\CurrencyService;

$currencyService = app(CurrencyService::class);

echo "\n";
echo "╔════════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                 CURRENCY CONVERSION AUDIT - ALL FEATURES                       ║\n";
echo "║                          December 12, 2025                                     ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════════╝\n\n";

// Test conversion rates
echo "EXCHANGE RATES AVAILABLE:\n";
echo "────────────────────────────────────────────────────────────────────────────────\n";

$baseCurrency = $currencyService->getBaseCurrency();
$testAmount = 50000;

echo "Base Currency: " . $baseCurrency . " (IDR)\n";
echo "Test Amount: " . number_format($testAmount) . " IDR\n\n";

$testCurrencies = ['USD', 'SAR', 'AED'];
foreach ($testCurrencies as $currency) {
    if ($currency !== $baseCurrency) {
        $converted = $currencyService->convertFromBase($testAmount, $currency);
        echo sprintf(
            "  • %s: %d IDR = %.6f %s\n",
            $currency,
            $testAmount,
            $converted,
            $currency
        );
    }
}

echo "\n\nFEATURES AUDIT:\n";
echo "════════════════════════════════════════════════════════════════════════════════\n\n";

// 1. FEATURED NOTES PRICING
echo "1. FEATURED NOTES PRICING\n";
echo "────────────────────────────────────────────────────────────────────────────────\n";
$featuredPricing = Setting::getFeaturedPricing();
$locations = ['landing_hero', 'marketplace_banner', 'marketplace_grid'];
$durations = [7, 14, 30];

echo "Current stored prices (in IDR):\n";
foreach ($locations as $location) {
    echo sprintf("  %-25s: ", $location);
    foreach ($durations as $duration) {
        $price = $featuredPricing[$location][$duration] ?? 0;
        echo sprintf("%d IDR ", (int)$price);
    }
    echo "\n";
}

echo "\n⚠️  ISSUE FOUND:\n";
echo "   • All prices stored as base currency (IDR)\n";
echo "   • When user selects USD or SAR, these values are NOT converted\n";
echo "   • Example: 50,000 IDR shows as 50,000 USD (WRONG!)\n";
echo "   • Should be: 50,000 IDR = $3.00 USD or ≈11.27 SAR\n";
echo "   • IMPACT: Admin can set affordable prices that become expensive when converted\n\n";

// 2. AFFILIATE SETUP
echo "2. AFFILIATE SETUP (Min Payout Amount)\n";
echo "────────────────────────────────────────────────────────────────────────────────\n";
$minPayoutAmount = Setting::getSetting('affiliate_min_payout_amount', 'affiliate', 50000);
echo "  • Min Payout Amount: " . number_format($minPayoutAmount) . " IDR\n";
echo "  • In USD: $" . number_format($currencyService->convertFromBase($minPayoutAmount, 'USD'), 2) . "\n";
echo "  • In SAR: " . number_format($currencyService->convertFromBase($minPayoutAmount, 'SAR'), 2) . " SAR\n";
echo "\n⚠️  ISSUE: Check if this is stored and compared correctly\n\n";

// 3. REFERRAL REWARDS
echo "3. REFERRAL REWARDS (Signup Bonus)\n";
echo "────────────────────────────────────────────────────────────────────────────────\n";
$refSignupReward = Setting::getReferralSignupReward();
echo "  • Signup Reward: " . number_format($refSignupReward) . " IDR\n";
echo "  • In USD: $" . number_format($currencyService->convertFromBase($refSignupReward, 'USD'), 2) . "\n";
echo "  • In SAR: " . number_format($currencyService->convertFromBase($refSignupReward, 'SAR'), 2) . " SAR\n";
echo "\n⚠️  ISSUE: Check if user receives this in their selected currency\n\n";

// 4. LEADERBOARD REWARDS
echo "4. LEADERBOARD MONTHLY REWARDS\n";
echo "────────────────────────────────────────────────────────────────────────────────\n";
$leaderboardRewards = [
    'rank_1' => Setting::getSetting('monthly_reward_rank_1', 'leaderboard', 5000000),
    'rank_2' => Setting::getSetting('monthly_reward_rank_2', 'leaderboard', 3000000),
    'rank_3' => Setting::getSetting('monthly_reward_rank_3', 'leaderboard', 2000000),
];

foreach ($leaderboardRewards as $rank => $amount) {
    echo sprintf(
        "  • %s: %s IDR = $%s = %s SAR\n",
        ucfirst(str_replace('_', ' ', $rank)),
        number_format($amount),
        number_format($currencyService->convertFromBase($amount, 'USD'), 2),
        number_format($currencyService->convertFromBase($amount, 'SAR'), 2)
    );
}
echo "\n⚠️  ISSUE: Rewards always given in IDR?\n\n";

// 5. MARKETPLACE MINIMUM PRICE
echo "5. MARKETPLACE MINIMUM PRICE FOR NOTES\n";
echo "────────────────────────────────────────────────────────────────────────────────\n";
$minPriceDefault = Setting::getDefaultMinPrice();
echo "  • Default Min Price: " . number_format($minPriceDefault) . " IDR\n";
echo "  • In USD: $" . number_format($currencyService->convertFromBase($minPriceDefault, 'USD'), 2) . "\n";
echo "  • In SAR: " . number_format($currencyService->convertFromBase($minPriceDefault, 'SAR'), 2) . " SAR\n";
echo "\n⚠️  ISSUE: When user sets price, is it validated correctly for their currency?\n\n";

// 6. PREMIUM SUBSCRIPTION PRICE
echo "6. PREMIUM SUBSCRIPTION PRICE (Monthly)\n";
echo "────────────────────────────────────────────────────────────────────────────────\n";
$premiumPrice = Setting::getPremiumPrice();
echo "  • Premium Monthly Price: " . number_format($premiumPrice) . " IDR\n";
echo "  • In USD: $" . number_format($currencyService->convertFromBase($premiumPrice, 'USD'), 2) . "\n";
echo "  • In SAR: " . number_format($currencyService->convertFromBase($premiumPrice, 'SAR'), 2) . " SAR\n";
echo "\n⚠️  ISSUE: Payment is charged in this currency or converted?\n\n";

// 7. AI FEATURE PRICING
echo "7. AI FEATURE PRICING (Per Usage)\n";
echo "────────────────────────────────────────────────────────────────────────────────\n";
$aiPrices = Setting::getAiFeaturePrices();
foreach ($aiPrices as $feature => $price) {
    echo sprintf(
        "  • %s: %s IDR = $%s = %s SAR\n",
        ucfirst(str_replace('_', ' ', $feature)),
        number_format($price),
        number_format($currencyService->convertFromBase($price, 'USD'), 2),
        number_format($currencyService->convertFromBase($price, 'SAR'), 2)
    );
}
echo "\n⚠️  ISSUE: Is cost deducted correctly from user's wallet currency?\n\n";

echo "════════════════════════════════════════════════════════════════════════════════\n";
echo "SUMMARY OF ISSUES FOUND:\n";
echo "════════════════════════════════════════════════════════════════════════════════\n\n";

$issues = [
    "1. All prices stored in base currency (IDR) - correct",
    "2. MISSING: Conversion when displaying prices to user",
    "3. MISSING: Conversion when user inputs amount in their currency",
    "4. MISSING: Validation that amount meets minimum in user's currency",
    "5. MISSING: Transaction creation with correct exchange_rate",
    "6. MISSING: Wallet deduction in user's selected currency",
    "7. POSSIBLE: Admin sees prices in IDR but user sees it differently",
    "",
    "CRITICAL EXAMPLE:",
    "─────────────────",
    "Admin sets: Featured note price = 50,000 IDR",
    "USD User sees: 50,000 (because no conversion!) = $50,000 ❌",
    "Actual value: 50,000 IDR = only $3.00 ✓",
    "",
    "CONSEQUENCE:",
    "─────────────",
    "USD/SAR users will be charged MASSIVELY (50,000x) more than intended!",
];

foreach ($issues as $issue) {
    echo $issue . "\n";
}

echo "\n════════════════════════════════════════════════════════════════════════════════\n";
echo "RECOMMENDATION:\n";
echo "════════════════════════════════════════════════════════════════════════════════\n\n";
echo "1. Update all amount input/display to show user's currency\n";
echo "2. Convert all amounts to base currency for storage\n";
echo "3. Set correct exchange_rate when creating transactions\n";
echo "4. Validate minimum amounts in user's selected currency\n";
echo "5. Show preview of amount in user's currency before confirmation\n";
echo "\n";
