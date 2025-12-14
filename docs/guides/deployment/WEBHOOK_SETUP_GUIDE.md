# Midtrans Webhook Setup & Troubleshooting Guide

## ✅ Status Saat Ini

Berdasarkan screenshot Midtrans dashboard, **webhook sudah dikonfigurasi:**

```
Payment Notification URL: https://noteds.com/wallet/webhook
Recurring Payment URL: https://noteds.com/payment/callback
```

## 🔍 Bagaimana Webhook Bekerja

### Flow Normal (Ideal):
```
1. User melakukan top-up di Noteds
2. User bayar via Midtrans Snap (payment page)
3. User konfirmasi pembayaran
4. Midtrans memproses pembayaran
5. ✅ Midtrans settlement → Status OK
6. Midtrans kirim webhook ke: https://noteds.com/wallet/webhook
7. Noteds terima notifikasi → Update database
8. Wallet user ter-update otomatis
```

### Flow Saat Ini (Ada masalah):
```
1. ✅ User melakukan top-up di Noteds
2. ✅ User bayar via Midtrans
3. ✅ Midtrans settlement OK (uang masuk)
4. ❌ Webhook tidak ter-trigger ATAU tidak ter-proses
5. ❌ Database status masih pending
6. User harus refresh/check manual
```

---

## 🚀 Cara Fix Saat Ini

### **Option 1: Manual Sync (IMMEDIATE)**

Jika topup stuck pending padahal di Midtrans sudah settlement:

```bash
# Sync SEMUA pending topup
php artisan midtrans:sync-status --all

# Atau sync yang spesifik (pakai order_id dari Midtrans)
php artisan midtrans:sync-status topup-1765564392-0...
```

**Hasil:** ✅ Status auto-update, wallet ter-update, user dapat notifikasi

---

### **Option 2: Manual Webhook Test (DEBUG)**

Untuk verifikasi webhook bisa diterima:

```bash
# 1. Clear recent logs
> rm storage/logs/laravel.log

# 2. Test webhook endpoint (simulate Midtrans POST)
curl -X POST https://noteds.com/wallet/webhook \
  -H "Content-Type: application/json" \
  -d '{"order_id":"topup-1765564392-0...","transaction_status":"settlement","fraud_status":"accept","gross_amount":10000,"status_code":"200"}'

# 3. Check logs
cat storage/logs/laravel.log | grep -i "webhook\|midtrans"
```

**Expected Output in Logs:**
```
Webhook received from Midtrans
Webhook Payload: order_id=topup-...
```

---

## 🔧 Troubleshooting Checklist

### ✅ Sudah Benar:
- [x] Webhook URL sudah set di Midtrans: `https://noteds.com/wallet/webhook`
- [x] Route `/wallet/webhook` sudah terdaftar di `routes/web.php`
- [x] CSRF exempt sudah set (webhook tidak perlu CSRF token)
- [x] Signature verification aktif (prevent spoofing)

### ❓ Yang Mungkin Masalah:

#### 1. **CSP (Content Security Policy) Blocking**
Midtrans mungkin tidak bisa POST ke webhook karena CSP.

**Check:** Di browser console saat payment, ada error CSP?
```
"connect-src 'self'" ... blocked
```

**Fix (Already Applied):**
```php
// app/Http/Middleware/SecurityHeaders.php
"connect-src 'self' https://api.midtrans.com https://*.cloudflare.com ..."
```

#### 2. **Firewall/Hosting Blocking**
Hosting Anda mungkin block incoming POST dari Midtrans IP.

**Fix:**
- Whitelist Midtrans IP di firewall
- Contact hosting untuk allow webhook requests
- Cek firewall logs: `/var/log/syslog` atau control panel

#### 3. **SSL Certificate Issues**
HTTPS connection ke webhook failure.

**Test:**
```bash
curl -v https://noteds.com/wallet/webhook
```

Should return **200 OK** (with CSRF error is fine, means route accessible)

#### 4. **Server Timeout**
Webhook request timeout sebelum Midtrans terima response.

**Fix:** Optimize webhook handler (jangan banyak query)
- Use `DB::transaction()` (sudah ada ✅)
- Add indices untuk transaction queries
- Reduce external API calls

---

## 📊 Monitoring Webhook

### Check Webhook Logs:

```bash
# Real-time monitoring
tail -f storage/logs/laravel.log | grep -i "webhook"

# Last 50 webhooks
cat storage/logs/laravel.log | grep "Webhook received" | tail -50
```

### Check Midtrans Dashboard:

1. Go to: https://dashboard.midtrans.com/settings/payment/notification
2. Click **"View notification history"**
3. See list of webhooks Midtrans tried to send
4. Check if `https://noteds.com/wallet/webhook` received status

---

## 🎯 Long Term Solutions

### **Suggested Improvements:**

1. **Add Webhook Retry Logic**
   ```php
   // If webhook fails, Midtrans auto-retry up to 5x
   // (Already configured at Midtrans side)
   ```

2. **Add Webhook Verification Endpoint**
   ```php
   // GET /webhook/status (public, for debugging)
   // Shows recent webhooks received
   ```

3. **Add Periodic Sync Scheduled Task**
   ```php
   // Every 5 minutes: php artisan schedule:run
   // Sync pending topups from Midtrans API
   // Prevent stuck payments
   ```

4. **Better Monitoring Dashboard**
   ```php
   // Admin can see:
   // - Webhook received count
   // - Failed webhooks
   // - Pending topups needing sync
   ```

---

## ✅ Verification Checklist

- [ ] Webhook URL is accessible: https://noteds.com/wallet/webhook (POST should give 422 or 200)
- [ ] Recent topup payments are showing in logs
- [ ] Midtrans dashboard notification history shows attempts
- [ ] Sync command works: `php artisan midtrans:sync-status --all`
- [ ] Users receive success notification after payment settles
- [ ] Wallet balance updates automatically

---

## 🆘 If Still Not Working

1. **Check firewall/hosting:**
   - Whitelist Midtrans IP ranges
   - Check incoming POST logs

2. **Test endpoint directly:**
   ```bash
   curl -X POST https://noteds.com/wallet/webhook \
     -H "Content-Type: application/json" \
     -d @webhook-test.json
   ```

3. **Contact Midtrans Support:**
   - Share: Order ID, transaction ID
   - Share: Webhook logs from their dashboard
   - Share: Your laravel.log entries

4. **Fallback Solution:**
   - Use sync command daily: `php artisan midtrans:sync-status --all`
   - Or setup cron: `0 * * * * php artisan midtrans:sync-status --all`

---

## 📝 Command Reference

```bash
# Sync specific topup
php artisan midtrans:sync-status topup-1765564392-0...

# Sync all pending topups
php artisan midtrans:sync-status --all

# View recent webhook logs
tail -f storage/logs/laravel.log | grep "Webhook"

# Check transaction status
php artisan tinker
> \App\Models\Transaction::where('midtrans_order_id', 'topup-...')->first()
```

---

**Last Updated:** December 13, 2025
**Status:** Webhook configured & Sync command ready
