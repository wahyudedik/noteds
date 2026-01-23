# Marketplace & Clipper Payment Setup Guide

Dokumentasi lengkap untuk setup dan konfigurasi payment gateway Midtrans untuk fitur **Marketplace Digital** dan **Clipper Top-Up**.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Database Setup](#database-setup)
3. [Midtrans Configuration](#midtrans-configuration)
   - [Sandbox Setup](#sandbox-setup)
   - [Production Setup](#production-setup)
4. [Environment Variables](#environment-variables)
5. [File Storage Setup](#file-storage-setup)
6. [Running Migrations & Seeders](#running-migrations--seeders)
7. [Queue Configuration](#queue-configuration)
8. [Testing](#testing)
   - [Marketplace Payment Testing](#marketplace-payment-testing)
   - [Clipper Top-Up Testing](#clipper-top-up-testing)
9. [Troubleshooting](#troubleshooting)
 
## Prerequisites

- PHP 8.2+
- Laravel 11+
- MySQL/PostgreSQL database
- Composer
- Node.js & NPM (untuk frontend)
- Midtrans account (sandbox atau production)

## Database Setup

1. Pastikan database sudah dibuat:
   ```bash
   php artisan migrate
   ```

2. Run seeders untuk data awal:
   ```bash
   php artisan db:seed
   ```

   Atau run seeder spesifik:
   ```bash
   php artisan db:seed --class=MarketplaceSeeder
   ```

## Midtrans Configuration

### Sandbox Setup

Untuk testing dan development, gunakan Midtrans Sandbox:

1. **Daftar Akun Sandbox**
   - Kunjungi: https://dashboard.sandbox.midtrans.com/
   - Daftar akun baru atau login
   - Setelah login, Anda akan mendapatkan:
     - Server Key (untuk backend)
     - Client Key (untuk frontend)

2. **Setup Environment Variables**

   Tambahkan ke file `.env`:
   ```env
   MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxxxxxxxxxxx
   MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxxxxxxxxxxx
   MIDTRANS_IS_PRODUCTION=false
   MIDTRANS_MERCHANT_ID=
   ```

3. **Setup Webhook URL untuk Local Development**

   Untuk local development, gunakan **ngrok** atau tool serupa untuk expose local server:

   **Menggunakan ngrok:**
   ```bash
   # Install ngrok (jika belum ada)
   # Download dari: https://ngrok.com/download
   
   # Expose local server
   ngrok http 8000
   ```
   
   Copy HTTPS URL yang diberikan ngrok (contoh: `https://abc123.ngrok.io`)

   Di Midtrans Sandbox Dashboard → Settings → Configuration → Payment Notification URL:
   ```
   https://abc123.ngrok.io/payment/webhook
   ```

   **Alternative untuk local development:**
   - Gunakan **localtunnel**: `npx localtunnel --port 8000`
   - Atau gunakan **serveo**: `ssh -R 80:localhost:8000 serveo.net`

4. **Test Payment dengan Card Testing**

   Midtrans Sandbox menyediakan kartu kredit test:
   - **Card Number**: `4811 1111 1111 1114`
   - **CVV**: `123`
   - **Expiry**: Bulan/tahun masa depan (misal: `12/25`)
   - **3D Secure OTP**: `112233`

   Untuk testing payment methods lainnya, lihat: https://docs.midtrans.com/docs/testing-payment-gateway

### Production Setup

Untuk production environment:

1. **Daftar Akun Production**
   - Kunjungi: https://dashboard.midtrans.com/
   - Lengkapi verifikasi bisnis
   - Setelah disetujui, Anda akan mendapatkan:
     - Production Server Key
     - Production Client Key
     - Merchant ID (jika menggunakan automatic settlement)

2. **Update Environment Variables**

   Di server production, update `.env`:
   ```env
   MIDTRANS_SERVER_KEY=Mid-server-xxxxxxxxxxxxxxxxxxxxx
   MIDTRANS_CLIENT_KEY=Mid-client-xxxxxxxxxxxxxxxxxxxxx
   MIDTRANS_IS_PRODUCTION=true
   MIDTRANS_MERCHANT_ID=G123456789  # Jika menggunakan automatic settlement
   ```

3. **Setup Webhook & Redirect URLs**

   Di Midtrans Dashboard → Settings → Configuration, setup URL endpoints berikut:

   **Payment Notification URL** (Required)*
   ```
   https://noteds.com/payment/webhook
   ```
   - URL untuk menerima payment status notification dari Midtrans
   - Midtrans akan mengirim POST request ke URL ini
   - Pastikan server dapat menerima POST request dari Midtrans
   - **Note**: Untuk local development, gunakan ngrok atau similar tool

   **Finish Redirect URL** (Optional - untuk VT-Web/Core API)
   ```
   https://noteds.com/marketplace/orders
   ```
   - Customer di-redirect ke sini jika payment berhasil
   - **Note**: Jika menggunakan Snap (popup), redirect ini tidak digunakan

   **Unfinish Redirect URL** (Optional - untuk VT-Web/Core API)
   ```
   https://noteds.com/marketplace/orders
   ```
   - Customer di-redirect ke sini jika menekan "Back to Order Website" saat payment belum selesai
   - **Note**: Jika menggunakan Snap (popup), redirect ini tidak digunakan

   **Error Redirect URL** (Optional - untuk VT-Web/Core API)
   ```
   https://noteds.com/marketplace/orders
   ```
   - Customer di-redirect ke sini jika terjadi error saat payment
   - **Note**: Jika menggunakan Snap (popup), redirect ini tidak digunakan

   **Recurring Notification URL** (Optional - jika menggunakan recurring payment)
   ```
   https://noteds.com/payment/recurring
   ```
   - Untuk notification recurring payment (jika ada)
   - **Note**: Endpoint ini belum diimplementasikan di aplikasi, hanya untuk referensi

   **Pay Account Notification URL** (Optional - jika menggunakan pay account)
   ```
   https://noteds.com/payment/pay-account
   ```
   - Untuk notification pay account status (jika ada)
   - **Note**: Endpoint ini belum diimplementasikan di aplikasi, hanya untuk referensi

   **Important Notes:**
   - Untuk aplikasi ini yang menggunakan **Midtrans Snap (popup/iframe)**, hanya **Payment Notification URL** yang wajib di-setup
   - **Single Webhook Endpoint**: Satu endpoint `/payment/webhook` menangani semua transaksi (Marketplace Orders dan Clipper Top-Ups)
   - Sistem otomatis mendeteksi jenis transaksi berdasarkan format `order_id`:
     - Marketplace Orders: `ORD-YYYYMMDD-XXXXXX`
     - Clipper Top-Ups: `TOPUP-{id}`
   - Finish/Unfinish/Error redirect URLs hanya diperlukan jika menggunakan VT-Web atau Core API dengan redirect
   - Semua URL harus menggunakan **HTTPS** di production
   - Untuk local development dengan ngrok: `https://your-ngrok-url.ngrok.io/payment/webhook`

4. **Automatic Settlement (Opsional)**

   Jika ingin settlement otomatis ke rekening merchant:
   - Aktifkan fitur di Midtrans Dashboard
   - Set Merchant ID di `.env`
   - Update `midtrans_merchant_id` di database untuk user yang memerlukan automatic settlement

## Environment Variables

Tambahkan variabel berikut ke file `.env`:

```env
# Midtrans Configuration
MIDTRANS_SERVER_KEY=your_server_key_here
MIDTRANS_CLIENT_KEY=your_client_key_here
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_MERCHANT_ID=your_merchant_id_here

# Application
APP_URL=http://localhost:8000
APP_ENV=local

# Queue (untuk background jobs)
QUEUE_CONNECTION=database
```

## File Storage Setup

Sistem marketplace menggunakan disk `products` untuk menyimpan file produk digital:

1. **Create Directory**

   ```bash
   mkdir -p storage/app/products
   ```

   Atau otomatis akan dibuat saat pertama kali upload file.

2. **Permissions**

   Pastikan directory dapat ditulis:
   ```bash
   chmod -R 775 storage/app/products
   ```

3. **Storage Link (untuk images)**

   Untuk product images yang disimpan di public disk: 
   ```bash
   php artisan storage:link
   ```

## File Upload Limits & Configuration

Marketplace mendukung upload file produk digital hingga **50MB**. Konfigurasi berikut diperlukan:

### PHP Configuration

1. **Create/Update `.user.ini` file** di root directory:

   ```ini
   ; PHP Configuration for File Uploads
   upload_max_filesize = 50M
   post_max_size = 52M
   max_file_uploads = 20
   memory_limit = 256M
   ```

   **Note**: Jika menggunakan Herd/Valet, restart server setelah update:
   ```bash
   valet restart
   # atau
   herd restart
   ```

2. **Atau edit `php.ini`** secara langsung (jika memiliki akses):

   Cari dan update nilai berikut:
   ```ini
   upload_max_filesize = 50M
   post_max_size = 52M
   max_file_uploads = 20
   memory_limit = 256M
   ```

3. **Verify Configuration**

   Buat file `phpinfo.php` di public directory untuk check:
   ```php
   <?php phpinfo(); ?>
   ```
   
   Cari nilai:
   - `upload_max_filesize`: harus `50M`
   - `post_max_size`: harus `52M` atau lebih besar

### Laravel Validation

Sistem sudah dikonfigurasi untuk:
- **Digital file (file_download)**: Maksimal 50MB (`max:51200` KB)
- **Product image**: Maksimal 2MB (`max:2048` KB)

### Client-Side Validation

Frontend sudah include validasi file size sebelum upload:
- File size ditampilkan saat user memilih file
- Warning muncul jika file melebihi limit
- Submit button disabled jika file terlalu besar

### Troubleshooting Large File Uploads

**Error: "PostTooLargeException - Content Too Large"**

1. **Check PHP limits:**
   ```bash
   php -i | grep -E "upload_max_filesize|post_max_size"
   ```

2. **Restart web server** setelah update `.user.ini`:
   ```bash
   # Nginx
   sudo service nginx restart
   
   # Apache
   sudo service apache2 restart
   
   # PHP-FPM
   sudo service php-fpm restart
   ```

3. **Verify Laravel exception handler** di `bootstrap/app.php` sudah include handler untuk `PostTooLargeException`

4. **Check file size di browser:**
   - Browser console akan show file size saat user memilih file
   - Client-side validation akan block sebelum submit

**Error: "File terlalu besar" (Laravel validation)**

- Pastikan file tidak melebihi 50MB
- Check apakah file corrupt atau terkompresi
- Coba kompres file atau split menjadi multiple files

**Error: "Memory limit exceeded"**

- Increase `memory_limit` di PHP config (default: 256M)
- Consider menggunakan queue untuk process large files

### Recommended File Size Limits

- **Product Images**: 2MB (optimal untuk web)
- **Digital Products**: 50MB (video, software, courses)
- Untuk file lebih besar, pertimbangkan:
  - CDN atau cloud storage (S3, Google Cloud Storage)
  - External download links
  - Chunked upload (future enhancement)

## Running Migrations & Seeders

1. **Run semua migrations:**
   ```bash
   php artisan migrate
   ```

2. **Run seeders:**
   ```bash
   php artisan db:seed
   ```

   Atau run spesifik:
   ```bash
   # User seeder (termasuk admin)
   php artisan db:seed --class=UserSeeder
   
   # Marketplace seeder (sample products & orders)
   php artisan db:seed --class=MarketplaceSeeder
   ```

3. **Fresh migration dengan seeders:**
   ```bash
   php artisan migrate:fresh --seed
   ```

## Queue Configuration

Sistem menggunakan queue untuk memproses:
- Midtrans webhooks
- Email notifications
- Background jobs

1. **Setup Queue Connection**

   Di `.env`, set:
   ```env
   QUEUE_CONNECTION=database
   ```

2. **Run Queue Worker**

   Development:
   ```bash
   php artisan queue:work
   ```

   Production (dengan supervisor atau systemd):
   ```bash
   php artisan queue:work --daemon --tries=3
   ```

   Atau gunakan Laravel Horizon (opsional):
   ```bash
   php artisan horizon
   ```

## Testing

### Marketplace Payment Testing

**Test Payment Flow (Sandbox):**

1. Login sebagai user biasa
2. Buka marketplace: `/marketplace`
3. Pilih produk dan klik "Buy Now"
4. Akan redirect ke halaman payment
5. Gunakan card test:
   - Card: `4811 1111 1111 1114`
   - CVV: `123`
   - Expiry: `12/25`
   - OTP: `112233`
6. Setelah payment berhasil:
   - Order status berubah menjadi "paid"
   - Order status berubah menjadi "completed"
   - Seller balance bertambah
   - License key ter-generate
   - Buyer bisa download file (jika ada)
   - Email notification terkirim ke buyer dan seller

### Clipper Top-Up Testing

**Test Top-Up Flow (Sandbox):**

1. Login sebagai user dengan role Clipper
2. Buka Clipper Dashboard: `/clipper/dashboard`
3. Navigate ke Top-Up: `/clipper/top-ups/create`
4. Input amount (minimum: Rp 10,000)
5. Pilih payment method (e-wallet, virtual account, atau credit card)
6. Klik "Create Top-Up"
7. Akan redirect ke halaman payment dengan Midtrans Snap popup
8. Gunakan card test yang sama:
   - Card: `4811 1111 1111 1114`
   - CVV: `123`
   - Expiry: `12/25`
   - OTP: `112233`
9. Setelah payment berhasil:
   - Top-Up status berubah menjadi "success"
   - Creator wallet balance bertambah sesuai amount
   - Ledger entry terbuat untuk tracking
   - Notification terkirim ke user
   - User bisa lihat history di `/clipper/top-ups`

**Top-Up Order ID Format:**
- Format: `TOPUP-{topUpId}`
- Contoh: `TOPUP-550e8400-e29b-41d4-a716-446655440000`
- Webhook otomatis mendeteksi format ini dan memproses top-up

**Marketplace Order ID Format:**
- Format: `ORD-YYYYMMDD-XXXXXX`
- Contoh: `ORD-20260102-123456`
- Webhook otomatis mendeteksi format ini dan memproses order

### Test Withdrawal Flow

1. Login sebagai user yang memiliki balance
2. Buka: `/marketplace/withdrawals/create`
3. Request withdrawal (min 50,000)
4. Login sebagai admin
5. Buka: `/admin/withdrawals`
6. Approve withdrawal
7. Complete withdrawal (akan deduct balance user)

### Test Admin Dashboard

1. Login sebagai admin
2. Buka: `/admin/dashboard`
3. Lihat stats: pending withdrawals, total users, total sales
4. Manage withdrawals dari dashboard

## Troubleshooting

### Payment tidak terdeteksi

1. **Check webhook URL**
   - Pastikan **Payment Notification URL** benar di Midtrans Dashboard → Settings → Configuration
   - URL harus: `https://noteds.com/payment/webhook` (untuk Marketplace dan Clipper)
   - URL harus dapat diakses dari internet (HTTPS di production)
   - Untuk local development, gunakan ngrok atau similar tool
   - Test webhook dengan Midtrans Dashboard → Settings → Integration → Webhook Simulator

2. **Verify webhook endpoint dapat diakses**
   ```bash
   # Test dengan curl (ganti URL dengan yang sesuai)
   curl -X POST https://noteds.com/payment/webhook \
     -H "Content-Type: application/json" \
     -d '{"test": "data"}'
   ```
   - Endpoint harus return HTTP 200 (bahkan untuk invalid data)
   - Check response: `{"status":"error","message":"Order ID not found"}` untuk invalid data

3. **Check logs**
   ```bash
   tail -f storage/logs/laravel.log | grep -i midtrans
   ```
   Cari log dengan keyword:
   - `Midtrans webhook received` - webhook diterima
   - `Top-up processed successfully` - top-up berhasil
   - `Order marked as paid` - order berhasil
   - `Unknown order_id format` - format order_id tidak dikenal

4. **Verify server key**
   - Pastikan server key benar di `.env`
   - Check apakah `MIDTRANS_IS_PRODUCTION` sesuai dengan environment (false untuk sandbox, true untuk production)
   - Pastikan menggunakan Server Key yang sesuai dengan environment

5. **Check queue worker**
   - Pastikan queue worker running jika webhook diproses via queue
   ```bash
   php artisan queue:work
   ```

6. **Verify order/top-up exists**
   - **Untuk Marketplace**: Pastikan order dengan `order_number` yang sesuai ada di database
     - Check tabel `orders` untuk order yang payment status masih pending
     - Format order_number: `ORD-YYYYMMDD-XXXXXX`
   - **Untuk Clipper Top-Up**: Pastikan top-up dengan ID yang sesuai ada di database
     - Check tabel `top_ups` untuk top-up yang status masih `pending_payment`
     - Format order_id di Midtrans: `TOPUP-{topUpId}`

7. **Check CSRF Protection**
   - Pastikan webhook routes dikecualikan dari CSRF protection
   - Check `bootstrap/app.php` dan `app/Http/Middleware/VerifyCsrfToken.php`
   - Routes yang harus dikecualikan: `payment/webhook`, `payment/recurring`, `payment/pay-account`

### File download tidak bekerja

1. Check permissions:
   ```bash
   chmod -R 775 storage/app/products
   ```

2. Verify file exists di storage
3. Check order status (harus "paid")

### Balance tidak ter-update

1. Check queue worker running:
   ```bash
   php artisan queue:work
   ```

2. Check failed jobs:
   ```bash
   php artisan queue:failed
   ```

3. Check transaction logs:
   - **Marketplace**: Check database table `transactions` untuk seller balance
   - **Clipper**: Check database table `creator_wallets` dan `ledger_entries` untuk creator wallet balance

4. **Untuk Marketplace:**
   - Pastikan order sudah `payment_status = 'paid'` dan `status = 'completed'`
   - Check apakah `BalanceService::addBalance()` dipanggil di webhook handler
   - Verify seller balance di tabel `users` (kolom `balance`)

5. **Untuk Clipper Top-Up:**
   - Pastikan top-up sudah `status = 'success'`
   - Check apakah `TopUpService::processTopUpSuccess()` dipanggil
   - Verify creator wallet balance di tabel `creator_wallets`
   - Check ledger entry di tabel `ledger_entries` dengan `reference_type = 'topup'`

### Admin tidak bisa akses dashboard

1. Verify user role:
   ```sql
   SELECT id, email, role FROM users WHERE email = 'admin@noteds.com';
   ```

2. Pastikan middleware `admin` ter-register di `bootstrap/app.php`

3. Check middleware di route:
   ```php
   Route::middleware('admin')->group(function () {
       // admin routes
   });
   ```

## Production Checklist

Sebelum deploy ke production:

### Midtrans Configuration
- [ ] Update `MIDTRANS_IS_PRODUCTION=true`
- [ ] Set production Server Key dan Client Key
- [ ] Setup **Payment Notification URL** di Midtrans Dashboard: `https://noteds.com/payment/webhook`
- [ ] (Optional) Setup Recurring Notification URL: `https://noteds.com/payment/recurring`
- [ ] (Optional) Setup Pay Account Notification URL: `https://noteds.com/payment/pay-account`
- [ ] (Optional) Setup Finish/Unfinish/Error redirect URLs jika menggunakan VT-Web/Core API
- [ ] Verify webhook URL dapat diakses dari internet (HTTPS)
- [ ] Test webhook dengan Midtrans dashboard webhook simulator

### Infrastructure
- [ ] Setup queue worker (supervisor/systemd)
- [ ] Setup file storage (S3/CDN jika perlu)
- [ ] Enable SSL/HTTPS (required untuk Midtrans)
- [ ] Setup backup database secara rutin

### Testing
- [ ] Test Marketplace payment flow end-to-end
- [ ] Test Clipper Top-Up flow end-to-end
- [ ] Test webhook dengan berbagai transaction status (settlement, capture, pending, deny, expire, cancel)
- [ ] Test duplicate webhook (idempotency)
- [ ] Test withdrawal approval flow
- [ ] Monitor logs dan errors

### Security
- [ ] Verify CSRF protection excluded untuk webhook routes
- [ ] Verify webhook signature verification aktif
- [ ] Check logging tidak expose sensitive data

## Support & Resources

- **Midtrans Documentation**: https://docs.midtrans.com/
- **Midtrans Dashboard**: 
  - Sandbox: https://dashboard.sandbox.midtrans.com/
  - Production: https://dashboard.midtrans.com/
- **Laravel Documentation**: https://laravel.com/docs
- **Midtrans PHP SDK**: https://github.com/Midtrans/midtrans-php

## Security Notes

1. **Jangan commit `.env` file** - Server Key dan Client Key adalah credentials sensitive
2. **Gunakan HTTPS di production** - Penting untuk payment security
3. **Verify webhook signature** - Sistem sudah include verification
4. **Limit file upload size** - Configure di `.user.ini` atau `php.ini` dan Laravel validation
5. **Protect admin routes** - Pastikan middleware `admin` bekerja dengan benar
6. **File upload validation** - Client-side dan server-side validation sudah diimplementasikan
7. **Exception handling** - PostTooLargeException sudah di-handle dengan user-friendly error messages
