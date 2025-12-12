# Quick Reference: Currency Conversion Implementation

## All 6 Features - COMPLETE ✅

### Feature 1: Affiliate Payout
**File**: `app/Services/AffiliateService.php` (createPayoutRequest)
**What**: Convert payout to user's currency before storage
**Key Code**:
```php
$exchangeRate = 1;
$amountInBase = $amount;
if ($userCurrency !== $baseCurrency) {
    $exchangeRate = $currencyService->getExchangeRate($baseCurrency, $userCurrency);
    $amountInBase = $amount / $exchangeRate;
}

AffiliatePayout::create([
    'amount' => $amount,
    'currency' => $userCurrency,
    'original_amount' => $amountInBase,
    'original_currency' => $baseCurrency,
    'exchange_rate' => $exchangeRate,
]);
```

---

### Feature 2: Referral Bonus
**File**: `app/Services/ReferralService.php` (completeSignupReward)
**What**: Convert 5k IDR signup bonus to referrer's currency
**Key Code**:
```php
$rewardInReferrerCurrency = $signupReward;
$exchangeRate = 1;
if ($referrerCurrency !== $baseCurrency) {
    $rewardInReferrerCurrency = $currencyService->convertFromBase($signupReward, $referrerCurrency);
    $exchangeRate = $currencyService->getExchangeRate($baseCurrency, $referrerCurrency);
}

$referrer->wallet->balance += $rewardInReferrerCurrency;

Transaction::create([
    'amount' => $rewardInReferrerCurrency,
    'currency' => $referrerCurrency,
    'original_amount' => $signupReward,
    'original_currency' => $baseCurrency,
    'exchange_rate' => $exchangeRate,
]);
```

---

### Feature 3: Premium Subscription
**File**: `app/Http/Controllers/SubscriptionController.php` (store)
**What**: Convert 25k IDR price to user's currency
**Key Code**:
```php
$exchangeRate = 1;
$premiumInUserCurrency = $premiumPrice;
if ($userCurrency !== $baseCurrency) {
    $exchangeRate = $currencyService->getExchangeRate($baseCurrency, $userCurrency);
    $premiumInUserCurrency = $premiumPrice * $exchangeRate;
}

$wallet->balance -= $premiumInUserCurrency;

Transaction::create([
    'amount' => $premiumInUserCurrency,
    'currency' => $userCurrency,
    'original_amount' => $premiumPrice,
    'original_currency' => $baseCurrency,
    'exchange_rate' => $exchangeRate,
]);
```

---

### Feature 4: Marketplace Min Price
**File**: `app/Http/Requests/StoreNoteRequest.php` (validation)
**What**: Convert 50k IDR minimum to user's currency for validation
**Key Code**:
```php
$minPrice = Setting::getDefaultMinPrice();
$currencyService = app(\App\Services\CurrencyService::class);
$userCurrency = $currencyService->getUserCurrency($user);
$baseCurrency = $currencyService->getBaseCurrency();

$minPriceInUserCurrency = $minPrice;
if ($userCurrency !== $baseCurrency) {
    $minPriceInUserCurrency = $currencyService->convertFromBase($minPrice, $userCurrency);
}

if ($price < $minPriceInUserCurrency) {
    $validator->errors()->add('price', 'Minimum price is ' . currency($minPriceInUserCurrency));
}
```

---

### Feature 5: AI Features
**File**: `app/Services/AiUsageService.php`

#### Part A: buildPaidDecision()
**What**: Convert 2k/10k/25k IDR feature costs to user's currency
**Key Code**:
```php
$basePrice = Setting::getAiFeaturePrice($feature);
$userCurrency = $summary['currency'] ?? $baseCurrency;

$priceInUserCurrency = $basePrice;
$exchangeRate = 1;
if ($userCurrency !== $baseCurrency) {
    $exchangeRate = $currencyService->getExchangeRate($baseCurrency, $userCurrency);
    $priceInUserCurrency = $basePrice * $exchangeRate;
}

$allowed = (float) $wallet->balance >= $priceInUserCurrency;

return [
    'amount' => $priceInUserCurrency,
    'original_amount' => $basePrice,
    'original_currency' => $baseCurrency,
    'exchange_rate' => $exchangeRate,
];
```

#### Part B: recordUsage()
**What**: Create transaction record with currency tracking
**Key Code**:
```php
Transaction::create([
    'user_id' => $user->id,
    'type' => 'ai_feature',
    'amount' => $chargedAmount,
    'currency' => $currency,
    'original_amount' => $decision['original_amount'] ?? $basePrice,
    'original_currency' => $decision['original_currency'] ?? $baseCurrency,
    'exchange_rate' => $decision['exchange_rate'] ?? 1,
    'description' => "AI Feature: {$feature}",
]);
```

---

### Feature 6: Leaderboard Rewards
**File**: `app/Jobs/DistributeLeaderboardRewardsJob.php`
**What**: Convert monthly rewards to winner's currency
**Key Code**:
```php
$userCurrency = $this->currencyService->getUserCurrency($user);
$baseCurrency = $this->currencyService->getBaseCurrency();

$rewardInUserCurrency = $reward;
$exchangeRate = 1;
if ($userCurrency !== $baseCurrency) {
    $exchangeRate = $this->currencyService->getExchangeRate($baseCurrency, $userCurrency);
    $rewardInUserCurrency = $reward * $exchangeRate;
}

$userWallet->increment('balance', $rewardInUserCurrency);

Transaction::create([
    'user_id' => $userId,
    'type' => 'leaderboard_reward',
    'amount' => $rewardInUserCurrency,
    'currency' => $userCurrency,
    'original_amount' => $reward,
    'original_currency' => $baseCurrency,
    'exchange_rate' => $exchangeRate,
    'description' => "Leaderboard Monthly Reward - Rank {$rank}",
]);
```

---

## Testing Checklist

- [ ] Create USD test user (en_US locale)
- [ ] Create SAR test user (ar_SA locale)
- [ ] Test Affiliate: USD payout, SAR payout
- [ ] Test Referral: USD referrer, SAR referrer sign up
- [ ] Test Premium: USD subscription, SAR subscription
- [ ] Test Marketplace: USD pricing, SAR pricing validation
- [ ] Test AI: USD feature use, SAR feature use
- [ ] Test Leaderboard: USD winner, SAR winner
- [ ] Verify all transactions logged with exchange rates
- [ ] Verify wallet balances in correct currencies

---

## Common Methods Reference

**Get user currency**:
```php
$userCurrency = $currencyService->getUserCurrency($user);
```

**Get base currency**:
```php
$baseCurrency = $currencyService->getBaseCurrency();
```

**Get exchange rate**:
```php
$rate = $currencyService->getExchangeRate($from, $to);
```

**Convert from base to user's currency**:
```php
$converted = $baseAmount * $currencyService->getExchangeRate($baseCurrency, $userCurrency);
```

**Format with currency**:
```php
{{ currency($amount) }}  // Blade template
```

---

## Notes

- All amounts stored in **user's currency** in Transaction table
- Base currency (IDR) amount stored in `original_amount`
- Exchange rate stored for audit trail
- Wallet balance in user's currency
- All validations use converted amounts, not base IDR
