# Marketplace Digital - Setup Guide

Dokumentasi lengkap untuk setup dan konfigurasi Marketplace Digital dengan integrasi Midtrans.

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

3. **Test Payment dengan Card Testing**

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

3. **Setup Webhook URL**

   Di Midtrans Dashboard:
   - Settings → Configuration → Payment Notification URL
   - Set URL: `https://yourdomain.com/payment/webhook`
   - Pastikan server dapat menerima POST request dari Midtrans

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

### Test Payment Flow (Sandbox)

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
   - Seller balance bertambah
   - License key ter-generate
   - Buyer bisa download file (jika ada)

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
   - Pastikan webhook URL benar di Midtrans Dashboard
   - Test dengan tool seperti ngrok untuk local development

2. **Check logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Verify server key**
   - Pastikan server key benar
   - Check apakah `MIDTRANS_IS_PRODUCTION` sesuai dengan environment

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

3. Check transaction logs di database table `transactions`

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

- [ ] Update `MIDTRANS_IS_PRODUCTION=true`
- [ ] Set production Server Key dan Client Key
- [ ] Setup webhook URL di Midtrans Dashboard
- [ ] Test webhook dengan Midtrans dashboard
- [ ] Setup queue worker (supervisor/systemd)
- [ ] Setup file storage (S3/CDN jika perlu)
- [ ] Enable SSL/HTTPS
- [ ] Test payment flow end-to-end
- [ ] Test withdrawal approval flow
- [ ] Monitor logs dan errors
- [ ] Setup backup database secara rutin

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
4. **Limit file upload size** - Configure di `php.ini` dan Laravel validation
5. **Protect admin routes** - Pastikan middleware `admin` bekerja dengan benar

