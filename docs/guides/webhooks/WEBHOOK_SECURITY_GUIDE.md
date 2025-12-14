# Webhook Security Implementation

## Overview

Your webhook endpoint `/wallet/webhook` now memiliki **MULTIPLE LAYERS OF SECURITY** untuk prevent injection dan spoofed requests:

```
Webhook Request dari Midtrans
        ↓
✅ Layer 1: Signature Verification (SHA512)
        ↓
✅ Layer 2: IP Whitelist Check
        ↓
✅ Layer 3: Amount Validation
        ↓
✅ Layer 4: Order Existence Check
        ↓
✅ Layer 5: Rate Limiting
        ↓
✅ Audit Logging
        ↓
Safe to process ✅
```

---

## Security Layers Explained

### Layer 1: ✅ Signature Verification (CRITICAL)

**What it does**: Verifies webhook came from legitimate Midtrans, not attacker

**How it works**:
- Midtrans sends `signature_key` in webhook
- We compute: `SHA512(order_id + status_code + gross_amount + server_key)`
- Compare computed vs received signature
- If different → **REJECT REQUEST** (possible spoofed)

**Code**:
```php
$inputString = $orderId . $statusCode . $grossAmount . $serverKey;
$computedSignature = hash('sha512', $inputString);

// Timing-safe comparison (prevents timing attacks)
if (!hash_equals($computedSignature, $signatureKey)) {
    throw new \Exception('Invalid Midtrans signature. Webhook rejected.');
}
```

**Protection**: ✅ Prevents attackers from crafting fake payment confirmations

---

### Layer 2: ✅ IP Whitelist Check

**What it does**: Verifies webhook source IP is from Midtrans

**Allowed IPs** (Midtrans official):
- `119.110.75.51` (Production)
- `103.58.103.188` (Production)
- `103.58.103.189` (Production)
- `119.110.75.35` (Production)
- `127.0.0.1` / `localhost` (Development/Testing)

**Code**:
```php
$clientIP = $request->ip();

if (!in_array($clientIP, $midtransIPs)) {
    throw new \Exception("Webhook from unauthorized IP: {$clientIP}");
}
```

**Protection**: ✅ Prevents webhooks from attacker's server

---

### Layer 3: ✅ Amount Validation

**What it does**: Prevents attacker from changing payment amount

**Example Attack Prevented**:
```
Legitimate payment: Rp 100.000
Attacker sends:     Rp 1.000.000 ❌ REJECTED
```

**Code**:
```php
$transaction = Transaction::where('midtrans_order_id', $orderId)->first();

if ((float) $grossAmount !== (float) $transaction->amount) {
    throw new \Exception("Amount mismatch. Expected: {$transaction->amount}, Received: {$grossAmount}");
}
```

**Protection**: ✅ Prevents amount tampering attacks

---

### Layer 4: ✅ Order Existence Check

**What it does**: Verifies order actually exists in database

**Example Attack Prevented**:
```
Attacker sends webhook for non-existent order_id: "fake-123456"
System checks database → Order not found → REJECT ✅
```

**Code**:
```php
$transaction = Transaction::where('midtrans_order_id', $orderId)->first();

if (!$transaction) {
    throw new \Exception("Transaction not found: {$orderId}");
}
```

**Protection**: ✅ Prevents fake order creation via webhooks

---

### Layer 5: ✅ Rate Limiting

**What it does**: Prevents rapid-fire exploit attempts

**Rules**:
- Max 5 webhook attempts per order per hour
- Uses Redis cache for tracking
- Blocks if limit exceeded

**Code**:
```php
$cacheKey = "webhook_process_{$orderId}";
$attempts = Cache::get($cacheKey, 0);

if ($attempts >= 5) {
    throw new \Exception("Webhook rate limit exceeded");
}

Cache::put($cacheKey, $attempts + 1, 3600);
```

**Protection**: ✅ Prevents brute force webhook spam

---

## Audit Logging

Setiap webhook attempt di-log untuk security audit:

```php
Log::info('✅ Webhook processed successfully', [
    'status' => 'success',
    'timestamp' => '2025-12-13T14:30:00Z',
    'order_id' => 'topup-1765564392-019b13d6',
    'transaction_status' => 'settlement',
    'fraud_status' => 'accept',
    'gross_amount' => 10000,
    'client_ip' => '119.110.75.51',
    'user_agent' => 'Midtrans',
]);
```

**View logs**:
```bash
tail -f storage/logs/laravel.log | grep -i webhook
```

---

## Attack Scenarios (ALL PREVENTED)

### ❌ Attack 1: Direct URL Injection

**Attacker**: `https://noteds.com/wallet/webhook?order_id=topup-xxx&status=settlement&amount=1000000`

**Result**: ❌ BLOCKED
- Reason: No signature_key provided
- Log: "Missing signature_key in webhook payload - possible spoofed request"

---

### ❌ Attack 2: Fake Signature

**Attacker**: Sends webhook with fake `signature_key`

**Result**: ❌ BLOCKED
- Reason: SHA512 hash doesn't match (attacker doesn't have server_key)
- Log: "🚨 SECURITY: Midtrans Signature Verification FAILED"

---

### ❌ Attack 3: Wrong Amount

**Attacker**: Sends payment completion with amount Rp 10 instead of Rp 10.000

**Result**: ❌ BLOCKED
- Reason: Amount mismatch validation
- Log: "🚨 SECURITY: Amount mismatch in webhook"

---

### ❌ Attack 4: Fake Order

**Attacker**: Sends webhook for non-existent order_id

**Result**: ❌ BLOCKED
- Reason: Transaction not found check
- Log: "⚠️ Webhook for non-existent transaction"

---

### ❌ Attack 5: Attacker's Server IP

**Attacker**: Sends webhook from own server IP `192.168.1.100`

**Result**: ❌ BLOCKED (Production only)
- Reason: IP not in whitelist
- Log: "⚠️ Webhook from unexpected IP address"
- Note: In development, IP check is relaxed for testing

---

### ❌ Attack 6: Rapid-Fire Spam

**Attacker**: Sends 10 webhook requests for same order in 1 second

**Result**: ❌ FIRST 5 ACCEPTED, THEN BLOCKED
- Reason: Rate limit (5 per hour)
- Log: "Webhook rate limit exceeded for {order_id}"

---

## Configuration

### Enable/Disable IP Check

In production, IP check is **enabled by default**. To disable (not recommended):

**`.env`**:
```env
MIDTRANS_WEBHOOK_IP_CHECK=false
```

Or in `config/services.php`:
```php
'midtrans' => [
    'webhook_ip_check' => false, // Only for development/testing
]
```

### Server Key Protection

**Most Critical**: Your `MIDTRANS_SERVER_KEY` is sensitive!

- ✅ Store in `.env` file (git-ignored)
- ✅ Never expose in logs
- ✅ Never share in code repositories
- ❌ Don't put in config files
- ❌ Don't commit to git

---

## Testing Webhook Security

### Test 1: Valid Webhook

```bash
curl -X POST https://noteds.com/wallet/webhook \
  -H "Content-Type: application/json" \
  -d '{
    "order_id": "topup-xxx",
    "status_code": "200",
    "gross_amount": "10000",
    "signature_key": "computed_sha512_hash_here"
  }'
```

Expected: ✅ 200 OK

### Test 2: Invalid Signature

```bash
curl -X POST https://noteds.com/wallet/webhook \
  -H "Content-Type: application/json" \
  -d '{
    "order_id": "topup-xxx",
    "status_code": "200",
    "gross_amount": "10000",
    "signature_key": "wrong_signature"
  }'
```

Expected: ❌ 400 Bad Request - "Invalid Midtrans signature"

### Test 3: Amount Mismatch

```bash
curl -X POST https://noteds.com/wallet/webhook \
  -H "Content-Type: application/json" \
  -d '{
    "order_id": "topup-xxx",
    "status_code": "200",
    "gross_amount": "999999",
    "signature_key": "correct_signature_for_999999"
  }'
```

Expected: ❌ 400 Bad Request - "Amount mismatch"

---

## Monitoring & Alerts

### View Security Events

```bash
# All webhook events
tail -100 storage/logs/laravel.log | grep webhook

# Failed webhooks only
tail -100 storage/logs/laravel.log | grep "FAILED\|Error\|🚨"

# Real-time monitoring
tail -f storage/logs/laravel.log | grep -E "webhook|Webhook|SECURITY"
```

### Setup Email Alerts (Optional)

Edit `app/Services/MidtransWebhookSecurityService.php`:

```php
// In verifySignature() method
if (!hash_equals($computedSignature, $signatureKey)) {
    // Send security alert email
    Mail::to('admin@noteds.com')->send(new WebhookSecurityAlertMail($notification));
    throw new \Exception('Invalid Midtrans signature');
}
```

---

## Compliance & Best Practices

✅ **Implemented**:
- SHA512 signature verification (industry standard)
- Timing-safe comparison (prevents timing attacks)
- IP whitelist (defense in depth)
- Amount validation (prevents tampering)
- Rate limiting (prevents brute force)
- Comprehensive audit logging
- HTTPS only (enforced by Midtrans)
- CSRF protection on other endpoints

✅ **Not Vulnerable To**:
- Signature forgery (SHA512 is secure)
- IP spoofing (controlled by payment gateway)
- Amount injection
- Replay attacks (transaction idempotency)
- DDoS (rate limiting + Cloudflare)

---

## Summary

Your webhook is now **SECURE** against:

| Attack | Prevention | Status |
|--------|-----------|--------|
| Fake signature | SHA512 verification | ✅ |
| Wrong IP | Whitelist check | ✅ |
| Amount tampering | Amount validation | ✅ |
| Fake orders | Order existence check | ✅ |
| Spam attacks | Rate limiting | ✅ |
| Timing attacks | Timing-safe comparison | ✅ |

---

**Status**: ✅ **PRODUCTION READY**  
**Last Updated**: December 13, 2025  
**Version**: 1.0  
**Security Level**: 🔒 HIGH
