# 🚀 QUICK: Setup Webhook Midtrans (5 Menit)

**Tujuan**: Agar topup payment otomatis update status dari "Pending" → "Success" tanpa perlu manual command.

---

## 📌 Langkah-Langkah Setup

### 1️⃣ Login ke Midtrans Dashboard
```
URL: https://dashboard.midtrans.com
Login dengan akun Midtrans kamu
```

### 2️⃣ Cari Settings → Notification
Buka menu:
```
Settings → Configuration → Notification
```

Atau langsung ke:
```
https://dashboard.midtrans.com/settings/snap/notification
```

### 3️⃣ Setup HTTP Notification URL

Cari section: **"HTTP Notification URL"**

Masukkan URL ini:
```
https://noteds.com/wallet/webhook
```

Pastikan:
- ✅ Method: **POST**
- ✅ Status: **Active/Enabled**
- ✅ Format JSON

### 4️⃣ Save & Test

1. Click **"Save"** atau **"Update"**
2. Tunggu notifikasi "Notification URL updated successfully"
3. Klik **"Send Test Notification"** (jika ada)

### 5️⃣ Verifikasi di Server

SSH ke production server dan jalankan:

```bash
# Cek webhook logs
tail -f storage/logs/laravel.log | grep -i webhook

# Expected: 
# 🔔 Webhook received from Midtrans
# ✅ Top-up successful
```

---

## ✅ Cara Verifikasi Webhook Jalan

### Test 1: Cek di Midtrans Dashboard

1. Go to: **Notifications → History** (atau **Webhook Testing**)
2. Lihat daftar recent webhooks
3. Status should show **"Success"** (bukan Failed)
4. Jika Failed, click **"Resend"**

### Test 2: Manual Test Topup

1. Buka app: `https://noteds.com/wallet`
2. Click **"Isi Saldo"**
3. Isi nominal (misal: 10,000 IDR)
4. Bayar dengan QRIS atau payment method
5. Tunggu 3-5 detik
6. Refresh halaman → **Saldo harus langsung naik!** ✅

### Test 3: Check Server Logs

```bash
# SSH ke server
ssh root@noteds

# Masuk folder
cd /www/wwwroot/noteds.com

# Check logs real-time
tail -f storage/logs/laravel.log | grep -E "settlement|success|webhook"

# Output should show:
# [INFO] 🔄 Syncing transaction: topup-xxx
# [INFO] Midtrans Status: settlement
# [INFO] ✅ Wallet updated: +10000 IDR
# [INFO] Top-up successful
```

---

## 🔧 Jika Webhook Tidak Jalan

### Checklist Troubleshoot

- [ ] URL di Midtrans benar: `https://noteds.com/wallet/webhook`
- [ ] Status "Active/Enabled" di Midtrans
- [ ] Server firewall tidak block POST request
- [ ] SSL certificate valid
- [ ] `MIDTRANS_SERVER_KEY` di `.env` sama dengan di Dashboard

### Quick Fix Commands

```bash
# 1. Cek apakah endpoint accessible
curl -X POST https://noteds.com/wallet/webhook

# Output harus ada error tentang payload (bukan 404)
# Jika 404 → URL salah
# Jika 500 → Ada error di code

# 2. Check environment variable
grep MIDTRANS_SERVER_KEY /www/wwwroot/noteds.com/.env

# 3. Manual sync pending transactions
cd /www/wwwroot/noteds.com
php artisan midtrans:sync-status --all

# Output:
# 🔄 Found 8 pending transactions
# ✅ Synced: 8
# ❌ Failed: 0
```

---

## 📊 Expected Behavior After Setup

### Sebelum Setup (Manual)
```
1. User topup → Payment success
2. Status: PENDING ❌ (harus manual command)
3. php artisan midtrans:sync-status --all
4. Status: SUCCESS ✅
```

### Sesudah Setup (Otomatis Real-Time)
```
1. User topup → Payment success
2. Midtrans kirim webhook
3. Status: SUCCESS ✅ (instant, 2-3 detik)
4. Saldo otomatis naik
5. User dapat notifikasi
```

---

## 📞 Need Help?

| Issue | Solution |
|-------|----------|
| Webhook tidak dikirim | Cek notification history di Midtrans, resend manual |
| Status masih pending | Jalankan: `php artisan midtrans:sync-status --all` |
| Signature verification failed | Cek `MIDTRANS_SERVER_KEY` di `.env` |
| Server 500 error | Check: `tail -f storage/logs/laravel.log` |
| URL not accessible | Cek firewall, SSL certificate, DNS |

---

## 🎉 Success!

Setelah webhook jalan:
- ✅ Topup payment langsung update ke "Success"
- ✅ Saldo langsung naik tanpa delay
- ✅ User dapat notifikasi instant
- ✅ Tidak perlu manual sync command lagi

**Kalo udah test dan berhasil, dokumentasi lengkap ada di:** 
`MIDTRANS_WEBHOOK_REAL_TIME_SETUP.md`
