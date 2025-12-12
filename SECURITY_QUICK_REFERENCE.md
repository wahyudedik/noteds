# Payment Security Quick Reference Card

## 🚨 Critical: All Payment Updates Must Follow This Pattern

```php
DB::transaction(function () {
    // 1️⃣ LOCK - Lock affected wallets first
    $wallet = Wallet::where('user_id', $userId)
        ->lockForUpdate()  // ← REQUIRED
        ->firstOrCreate(['user_id' => $userId], ['balance' => 0]);

    // 2️⃣ VALIDATE - Check input amounts
    if (!is_numeric($amount) || is_nan($amount) || is_infinite($amount)) {
        throw new Exception('Invalid amount');
    }

    // 3️⃣ VALIDATE - Check calculated amounts
    if (!is_numeric($calculated) || is_nan($calculated) || is_infinite($calculated)) {
        throw new Exception('Invalid calculation');
    }

    // 4️⃣ UPDATE - Do the transaction
    $wallet->balance += $amount;
    $wallet->save();
    
    // 5️⃣ LEDGER - Create record
    Transaction::create([...]);
});
// Lock automatically released here ✅
```

---

## 📋 Validation Checklist Before Updating Wallets

For **every** payment amount:
- [ ] `is_numeric($amount)` - Is it a number?
- [ ] `!is_nan($amount)` - Not "Not a Number"?
- [ ] `!is_infinite($amount)` - Not infinity?
- [ ] `$amount > 0` - Positive (or >= 0 for commissions)?
- [ ] `$amount <= reasonable_limit` - Within bounds?

For **every** percentage:
- [ ] `0 <= $percent <= 100` - Valid range?
- [ ] Calculated fee isn't NaN/Infinite?

```php
// ❌ BAD - No validation
$wallet->balance -= $amount;
$wallet->save();

// ✅ GOOD - Validated
if (!is_numeric($amount) || is_nan($amount) || is_infinite($amount) || $amount <= 0) {
    throw new Exception('Invalid amount');
}
$wallet->balance -= $amount;
$wallet->save();
```

---

## 🔒 Wallet Update Locations (All Secured)

| Location | Purpose | Lock Status |
|----------|---------|---|
| **WalletController** | Top-ups | ✅ Locked |
| **MarketplaceController** | Note purchases | ✅ 4 wallets locked |
| **BuyerSubscriptionController** | Subscriptions | ✅ Locked |
| **ServiceOrderController** | Escrow operations | ✅ 4 methods locked |

---

## 🛡️ Webhook Security (Midtrans Payments)

```php
// ✅ CORRECT - Signature verified
private function verifyMidtransSignature(array $params, string $signature): bool
{
    $message = $params['order_id'] . $params['status_code'] . $params['gross_amount'];
    return hash_equals(
        hash('sha512', $message . config('services.midtrans.server_key')),
        $signature
    );
}

// Call this BEFORE updating any balances:
if (!$this->verifyMidtransSignature($params, $signature)) {
    // Reject webhook ❌
}

// ❌ WRONG - No signature verification
$transaction->status = 'success';
$transaction->save(); // Could be spoofed!
```

---

## ⚡ Rate Limiting (Wallet Top-ups)

```php
// Already configured: ThrottleWalletTopup.php
// Limits:
// - 5 requests per minute per user
// - 20 requests per hour per user

// Middleware auto-rejects with 429 Too Many Requests ✅
```

---

## 🔄 Race Condition Prevention

### ❌ WITHOUT lockForUpdate()
```
User A                          User B
|                              |
read balance (100)             read balance (100)
|                              |
deduct 50 → balance=50         deduct 50 → balance=50
|                              |
save                           save
|                              |
❌ WRONG: Balance should be 0, not 50!
```

### ✅ WITH lockForUpdate()
```
User A                          User B
|                              |
lock wallet                    waiting for lock...
read balance (100)             |
deduct 50 → balance=50         |
save                           waiting...
release lock                   |
|                              lock acquired
|                              read balance (50)
|                              deduct 50 → balance=0
|                              save ✅ CORRECT
```

---

## 💰 Amount Validation Examples

```php
// ✅ VALID amounts
$amount = 100;          // Whole number
$amount = 99.99;        // Decimal
$amount = 0.50;         // Small amount

// ❌ INVALID amounts (must reject)
$amount = 0/0;          // NaN
$amount = asin(2);      // NaN  
$amount = sqrt(-1);     // NaN
$amount = INF;          // Infinity
$amount = "string";     // Not numeric
$amount = -100;         // Negative
$amount = 0;            // Zero (usually invalid)

// Check with:
if (!is_numeric($amount) || is_nan($amount) || is_infinite($amount) || $amount <= 0) {
    // Reject
}
```

---

## 📊 Commission Validation Examples

```php
// ✅ VALID percentages
$commission = 20;       // 20%
$commission = 0;        // No commission
$commission = 50;       // 50%

// ❌ INVALID percentages (must reject)
$commission = -10;      // Negative
$commission = 150;      // Over 100%
$commission = NaN;      // Not a number

// Check with:
if (!is_numeric($commission) || $commission < 0 || $commission > 100) {
    // Reject
}

// Also validate calculation result:
$fee = $amount * ($commission / 100);
if (is_nan($fee) || is_infinite($fee)) {
    // Reject
}
```

---

## 🐛 Common Mistakes to Avoid

### ❌ WRONG: No transaction wrapper
```php
$wallet->balance -= $amount;
$wallet->save();
$seller->wallet_balance = $wallet->balance;
$seller->save(); // Could fail, leaving inconsistent state
```

### ✅ CORRECT: Wrapped in transaction
```php
DB::transaction(function () {
    $wallet->balance -= $amount;
    $wallet->save();
    $seller->wallet_balance = $wallet->balance;
    $seller->save(); // All-or-nothing
});
```

---

### ❌ WRONG: No locking
```php
$wallet = Wallet::firstOrCreate([...]);
// Another request here could read stale balance
$wallet->balance -= $amount;
$wallet->save();
```

### ✅ CORRECT: With locking
```php
$wallet = Wallet::where(...)->lockForUpdate()->firstOrCreate([...]);
// No other request can read/write until this transaction commits
$wallet->balance -= $amount;
$wallet->save();
```

---

### ❌ WRONG: No amount validation
```php
$amount = $request->input('amount');
// Could be NaN, Infinite, negative, etc.
$wallet->balance -= $amount;
$wallet->save();
```

### ✅ CORRECT: With validation
```php
$amount = (float) $request->input('amount');
if (!is_numeric($amount) || is_nan($amount) || is_infinite($amount) || $amount <= 0) {
    return back()->with('error', 'Invalid amount');
}
$wallet->balance -= $amount;
$wallet->save();
```

---

## 🚀 Production Deployment Checklist

- [ ] All payment controllers use DB::transaction()
- [ ] All wallet updates have lockForUpdate()
- [ ] All amounts validated (is_numeric + NaN + Infinite checks)
- [ ] All percentages bounds-checked (0-100)
- [ ] Webhook signature verification active
- [ ] Rate limiting middleware installed
- [ ] Cleanup command configured
- [ ] Error messages don't leak info
- [ ] Logs configured for monitoring
- [ ] Database backups automated
- [ ] Rollback plan documented

---

## 📞 Emergency Contacts

### If You Find a Payment Bug
1. **Don't ignore it** - Payment bugs are critical
2. **Isolate immediately** - Stop processing that payment method
3. **Check for balance inconsistency** - Query database for negative balances
4. **Notify admin** - Flag the issue immediately
5. **Roll back if needed** - Restore from backup if balance corrupted

### Security Issues
- Check: `grep -r "lockForUpdate" app/Http/Controllers/`
- Check: `grep -r "DB::transaction" app/Http/Controllers/`
- Check: `grep -r "is_nan\|is_infinite" app/Http/Controllers/`

---

## 📈 Monitoring Queries

```bash
# Check for negative balances (should be 0)
SELECT user_id, balance FROM wallets WHERE balance < 0;

# Check for stale pending transactions (> 24 hours)
SELECT * FROM transactions 
WHERE status = 'pending' 
AND created_at < NOW() - INTERVAL 24 HOUR;

# Check for webhook rejections (in logs)
grep "Signature verification failed" /logs/laravel.log | wc -l

# Check for rate limit violations
grep "Rate limit exceeded" /logs/laravel.log | tail -20
```

---

## 🎯 Key Takeaways

1. **ALWAYS** use `DB::transaction()` for payments
2. **ALWAYS** use `->lockForUpdate()` on wallet queries
3. **ALWAYS** validate amounts (is_numeric + NaN + Infinite)
4. **ALWAYS** validate percentages (0 ≤ x ≤ 100)
5. **NEVER** trust user input for amounts
6. **NEVER** skip webhook signature verification
7. **NEVER** update wallets outside a transaction

---

**Last Updated:** December 12, 2025  
**Status:** ✅ All Payment Methods Secured  
**Questions?** See SECURITY_AUDIT_PAYMENT_SYSTEM_FINAL.md
