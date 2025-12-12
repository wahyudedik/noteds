# Midtrans Webhook Real-Time Setup Guide

Dokumentasi lengkap untuk setup webhook Midtrans agar payment status update otomatis real-time tanpa perlu manual sync command.

## 📋 Checklist Setup

- [ ] Verify webhook endpoint is accessible
- [ ] Configure notification URL in Midtrans Dashboard
- [ ] Test webhook delivery
- [ ] Verify transaction status updates automatically

---

## 1️⃣ Webhook Endpoint Configuration

### Endpoint Details
- **URL**: `https://noteds.com/wallet/webhook`
- **Method**: POST
- **Auth**: No authentication required (signature-based verification)
- **CSRF**: Exempt (Midtrans doesn't use CSRF tokens)

### Webhook Handler Location
- **File**: `app/Http/Controllers/WalletController.php`
- **Method**: `webhook()` (line 268)
- **Security**: Uses SHA512 signature verification to prevent spoofed webhooks

---

## 2️⃣ Midtrans Dashboard Configuration

### Step 1: Access Midtrans Dashboard
1. Go to https://dashboard.midtrans.com
2. Login with your Midtrans account
3. Select your business (G445421590)

### Step 2: Configure Payment Notification URL
1. Navigate to: **Settings → Notification** (or **Configuration → Notification Center**)
2. Look for **HTTP Notification URL** section
3. Set the following URLs:

#### Payment Notification (Settlement Notification)
- **URL**: `https://noteds.com/wallet/webhook`
- **HTTP Method**: POST
- **Status**: Active/Enabled

#### (Optional) Recurring Payment Notification
- **URL**: `https://noteds.com/payment/callback`
- **HTTP Method**: POST
- **Status**: Active/Enabled

### Step 3: Save Configuration
- Click "Save" or "Update"
- You should see confirmation message: "Notification URL updated successfully"

---

## 3️⃣ Testing Webhook Delivery

### Method 1: Using Midtrans Dashboard (Recommended)

1. In Midtrans Dashboard, go to **Notification History** or **Webhook Testing**
2. Find a recent transaction
3. Click **"Resend Notification"** button
4. Check Laravel logs for webhook receipt:

```bash
# SSH into production server
tail -f storage/logs/laravel.log | grep -i "webhook"

# Expected output:
# [2025-12-13 15:30:45] local.INFO: 🔔 Webhook received from Midtrans
# [2025-12-13 15:30:45] local.INFO: Webhook Payload: {"order_id":"topup-xxx","transaction_status":"settlement",...}
```

### Method 2: Manual Test Transaction

1. Go to your app: `https://noteds.com/wallet`
2. Click "Isi Saldo" (Top-up)
3. Complete payment using Midtrans Snap
4. On production, check logs immediately after payment:

```bash
tail -f storage/logs/laravel.log | grep -E "Webhook|settlement|capture"
```

### Method 3: Postman/cURL Test (for development only)

```bash
# Example webhook payload
curl -X POST https://noteds.com/wallet/webhook \
  -H "Content-Type: application/json" \
  -d '{
    "order_id": "topup-1765564392-019b13d6",
    "transaction_status": "settlement",
    "fraud_status": "accept",
    "gross_amount": "10000.00",
    "status_code": "200",
    "signature_key": "XXXXXXXXXXXX"
  }'
```

---

## 4️⃣ How It Works (Flow)

### Real-Time Flow:
```
┌─────────────┐
│   User      │
│  TopUp Flow │
└──────┬──────┘
       │
       ▼
┌─────────────────────────────────────────┐
│  1. User submits topup request (IDR)    │
│  2. App creates Transaction (pending)   │
│  3. Midtrans Snap loads payment form    │
└──────────────────┬──────────────────────┘
                   │
                   ▼
        ┌──────────────────────┐
        │  2. User pays QRIS   │
        │     or other method  │
        └──────────┬───────────┘
                   │
                   ▼
    ┌──────────────────────────────────┐
    │  3. Midtrans receives payment    │
    │  4. Payment settles (settlement) │
    │  5. Midtrans sends webhook POST  │
    └──────────────────┬───────────────┘
                       │
                       ▼
        ┌──────────────────────────────────────┐
        │  6. App receives webhook at:         │
        │     POST /wallet/webhook             │
        │                                      │
        │  7. Verify Midtrans signature (SHA) │
        │  8. Lock transaction row (prevent    │
        │     race condition)                  │
        │  9. Update transaction status →      │
        │     "success"                        │
        │ 10. Credit user wallet balance       │
        │ 11. Send success notification        │
        │ 12. Log everything                   │
        └──────────────┬───────────────────────┘
                       │
                       ▼
         ┌──────────────────────────────┐
         │  User wallet balance updated │
         │  Transaction shows "Success" │
         │  User receives notification  │
         └──────────────────────────────┘
```

### Key Features:
- ✅ **Real-time**: Updates happen within seconds of payment settlement
- ✅ **Secure**: SHA512 signature verification prevents spoofed webhooks
- ✅ **Safe**: Database locking prevents duplicate processing
- ✅ **Idempotent**: Running webhook twice doesn't double-credit
- ✅ **Logged**: All webhook events logged for auditing

---

## 5️⃣ Monitoring & Debugging

### Check Webhook Logs

```bash
# View all webhook attempts
grep "Webhook received from Midtrans" storage/logs/laravel.log

# View failed webhooks
grep "Webhook Error" storage/logs/laravel.log

# View signature verification failures
grep "Signature Verification Failed" storage/logs/laravel.log

# View successful transactions
grep "Top-up successful" storage/logs/laravel.log
```

### Common Issues & Fixes

#### Issue 1: Webhook not being received
**Symptoms**: Logs don't show "🔔 Webhook received from Midtrans"

**Solutions**:
1. Verify webhook URL in Midtrans Dashboard is correct
2. Check firewall/WAF is not blocking POST requests
3. Verify HTTPS certificate is valid
4. Check if /wallet/webhook route is accessible: `curl https://noteds.com/wallet/webhook` (should give 405 Method Not Allowed for GET)

#### Issue 2: Signature verification failed
**Symptoms**: Logs show "Signature Verification Failed" or "Invalid Midtrans signature"

**Causes**:
- Midtrans Server Key mismatch in config
- Webhook payload modified by proxy/firewall
- Midtrans Server Key changed in dashboard

**Fix**:
1. Verify `MIDTRANS_SERVER_KEY` in `.env` matches Midtrans Dashboard
2. Check if API firewall is modifying payload
3. Regenerate Server Key if compromised

#### Issue 3: Transaction not found
**Symptoms**: Logs show "Transaction not found for order_id"

**Causes**:
- Transaction record deleted
- Order ID mismatch

**Fix**:
1. Verify order_id format is correct (should be `topup-TIMESTAMP-ULID`)
2. Check database for transaction record

#### Issue 4: Duplicate processing (balance credited twice)
**This shouldn't happen** due to locking, but if it does:

**Fix**:
1. Check logs for concurrent webhook calls
2. Verify database locks are working: `SELECT * FROM information_schema.innodb_locks;`
3. Restart webhook handler if needed

---

## 6️⃣ Fallback: Manual Sync Command

If webhooks fail, you can manually sync pending transactions:

```bash
# Sync all pending transactions with Midtrans API
php artisan midtrans:sync-status --all

# Sync specific transaction
php artisan midtrans:sync-status topup-1765564392-019b13d6
```

Expected output:
```
🔄 Syncing transaction: topup-1765564392-019b13d6
Current DB Status: pending
Midtrans Status: settlement
Fraud Status: accept
⚠️  Payment is settled in Midtrans but not updated in DB. Processing...
✅ Wallet updated: +10000 IDR
New Balance: 20000
✅ Transaction synced successfully!
```

---

## 7️⃣ Cron Job Setup (Optional but Recommended)

For extra reliability, setup a cron job to sync pending transactions every 5 minutes:

```bash
# Edit crontab
crontab -e

# Add this line (runs every 5 minutes):
*/5 * * * * cd /www/wwwroot/noteds.com && php artisan midtrans:sync-status --all > /dev/null 2>&1
```

This acts as a safety net if webhooks are delayed or fail.

---

## 8️⃣ Webhook Security Best Practices

### ✅ What We're Doing Right

1. **Signature Verification** (SHA512)
   ```php
   // Reconstructed in webhook handler
   $inputString = $orderId . $statusCode . $grossAmount . $serverKey;
   $computedSignature = hash('sha512', $inputString);
   hash_equals($computedSignature, $signatureKey); // Timing-safe comparison
   ```

2. **Idempotency Check** (Prevent duplicate processing)
   ```php
   if ($transaction->status === 'success') {
       return response()->json(['status' => 'ok']); // Skip processing
   }
   ```

3. **Pessimistic Locking** (Database level)
   ```php
   $transaction->lockForUpdate()->refresh(); // Prevent race conditions
   ```

4. **Comprehensive Logging**
   ```php
   Log::info('Webhook received', ['order_id', 'ip', 'timestamp']);
   Log::error('Webhook error', ['trace', 'payload']);
   ```

5. **CSRF Exempt** (Midtrans doesn't send CSRF tokens)
   ```php
   Route::post('/wallet/webhook', ...) ->withoutMiddleware(VerifyCsrfToken::class);
   ```

---

## 📞 Support

If webhooks still not working after this setup:

1. **Check Midtrans Notification History**
   - Dashboard → Notifications → History
   - See if Midtrans attempted to send webhook

2. **Contact Midtrans Support**
   - Email: support@midtrans.com
   - Website: https://support.midtrans.com
   - Provide: Merchant ID (G445421590), error logs, order ID

3. **Check Server Logs**
   ```bash
   # Check web server access logs
   tail -f /var/log/nginx/access.log | grep "wallet/webhook"
   
   # Check firewall
   sudo ufw status
   
   # Check Laravel error logs
   tail -f storage/logs/laravel.log
   ```

---

## ✅ Success Criteria

Webhooks are properly configured when:
- [ ] Payment status updates to "Success" within 5 seconds of payment
- [ ] User wallet balance increases automatically
- [ ] Logs show "Top-up successful" message
- [ ] No duplicate credits (balance only increases once)
- [ ] Signature verification passes for all webhooks
- [ ] Notification sent to user

**Once webhook working, you no longer need manual sync commands!** 🎉
