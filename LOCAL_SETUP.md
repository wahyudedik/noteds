# 🚀 Noteds — Local Development Setup Guide

## 📋 Prerequisites

- PHP 8.2+ (via Laragon/Herd/XAMPP)
- Composer 2.x
- Node.js 18+ (recommended) or 20+ (for Herd HTTPS compatibility)
- MySQL/MariaDB
- Git

## 🛠️ Installation Steps

### 1. Clone Repository
```bash
git clone <repository-url>
cd noteds
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```env
APP_NAME="Noteds"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://noteds.test  # Use HTTP for development to avoid mixed content errors

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=noteds_db
DB_USERNAME=root
DB_PASSWORD=

MIDTRANS_SERVER_KEY=your_sandbox_key_here
MIDTRANS_CLIENT_KEY=your_sandbox_key_here
MIDTRANS_IS_PRODUCTION=false

OLLAMA_URL=http://localhost:11434
OLLAMA_MODEL=llama3.2
```

### 4. Database Setup
```bash
php artisan migrate
php artisan db:seed
```

### 5. Storage Link
```bash
php artisan storage:link
```

### 6. Queue Worker (Email & Forum Notifications)
```bash
php artisan queue:work
```
> Jalankan di terminal terpisah selama development untuk memproses email notifikasi forum dan job background lainnya.

### 7. Frontend Assets

#### Using Herd - Recommended
```bash
npm install
npm run dev  # For development with hot reload
# OR
npm run build  # For production build (no hot reload)
```

**⚠️ Important:** 
- **Akses via HTTP:** `http://noteds.test` (bukan https) untuk menghindari Mixed Content errors
- Jika Herd menggunakan HTTPS, disable HTTPS di Herd settings atau akses langsung via HTTP
- `npm run dev` menggunakan Vite dev server (HTTP) yang tidak kompatibel dengan HTTPS pages

#### Using `php artisan serve` (HTTP)
```bash
npm install
npm run dev
```

Run in another terminal:
```bash
php artisan serve
```

Access: `http://localhost:8000`

### 8. Optional: Run Scheduler Locally
```bash
php artisan schedule:work
```
> Menjaga command terjadwal seperti `forum:publish-scheduled-posts` aktif selama pengembangan.

### 9. Development Tools

#### Laravel Telescope (Debugging)
Access: `/telescope` (local only, admin only in production)

#### Laravel Debugbar
Auto-injected in local environment

#### Laravel Pint (Code Style)
```bash
composer pint
```

#### Pest Testing
```bash
./vendor/bin/pest
```

## 🔧 Common Commands

### Cache Management
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### Fresh Migration
```bash
php artisan migrate:fresh --seed
```

### Serve Development
```bash
php artisan serve
```

### Assets Development
```bash
npm run dev      # With php artisan serve
npm run build    # With Herd/Vite (production build)
```

### Subscription Auto-Renewal (Testing)
```bash
php artisan subscriptions:renew
```

### Publish Scheduled Forum Posts (Testing)
```bash
php artisan forum:publish-scheduled-posts
```

**Note:** This command is automatically scheduled to run daily at 00:00 WIB in production (see `routes/console.php`). In development, you can run it manually to test subscription renewal logic.

**What it does:**
- Checks active premium subscriptions expiring today or tomorrow
- Auto-renews if wallet balance is sufficient
- Expires subscription and sends notifications if balance is insufficient
- See [VPS_SETUP.md](VPS_SETUP.md) for detailed documentation

## 💳 Testing Midtrans Payment Gateway di Local

### Setup Midtrans Sandbox untuk Local Development

**PENTING:** Midtrans **BISA** di-test di local, tidak harus di VPS!

#### 1. Dapatkan Sandbox Keys
1. Login ke [Midtrans Sandbox Dashboard](https://dashboard.sandbox.midtrans.com)
2. Navigate ke **Settings** → **Access Keys**
3. Copy **Server Key** dan **Client Key** (yang dimulai dengan `SB-Mid-`)

#### 2. Konfigurasi `.env` untuk Local
```env
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxx  # Sandbox Server Key
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxx  # Sandbox Client Key
MIDTRANS_IS_PRODUCTION=false  # Pastikan false untuk sandbox
MIDTRANS_MERCHANT_ID=Gxxxxx  # Sandbox Merchant ID (optional)
```

#### 3. Konfigurasi URL Endpoints di Midtrans Dashboard

**Untuk Local Testing, ada 2 opsi:**

##### Opsi A: Skip Webhook (Paling Mudah untuk Testing Dasar)
- **Payment Notification URL:** Bisa dikosongkan atau isi dummy URL
- **Finish/Unfinish/Error Redirect URLs:** Gunakan localhost
  ```
  http://noteds.test/payment/finish
  http://noteds.test/payment/unfinish
  http://noteds.test/payment/error
  ```
- **Catatan:** Webhook tidak akan diterima, tapi redirect setelah payment tetap berfungsi
- **Untuk update status:** Bisa manual check di Midtrans Dashboard atau gunakan polling

##### Opsi B: Gunakan Ngrok untuk Webhook (Testing Lengkap)
1. **Install Ngrok:**
   ```bash
   # Download dari https://ngrok.com/download
   # Atau via npm: npm install -g ngrok
   ```

2. **Start Ngrok Tunnel:**
   ```bash
   ngrok http 80  # Jika menggunakan Herd (port 80)
   # atau
   ngrok http 8000  # Jika menggunakan php artisan serve (port 8000)
   ```

3. **Dapatkan Ngrok URL:**
   - Ngrok akan memberikan URL seperti: `https://abc123.ngrok.io`
   - URL ini bisa diakses dari internet

4. **Konfigurasi di Midtrans Dashboard:**
   - **Payment Notification URL:** `https://abc123.ngrok.io/payment/callback`
   - **Finish Redirect URL:** `https://abc123.ngrok.io/payment/finish`
   - **Unfinish Redirect URL:** `https://abc123.ngrok.io/payment/unfinish`
   - **Error Redirect URL:** `https://abc123.ngrok.io/payment/error`

5. **Update `.env` untuk Ngrok:**
   ```env
   APP_URL=https://abc123.ngrok.io  # Gunakan ngrok URL
   ```

#### 4. Test Payment Flow di Local

1. **Start Laravel:**
   ```bash
   php artisan serve
   # atau gunakan Herd: http://noteds.test
   ```

2. **Test Top-up:**
   - Buka `http://noteds.test/wallet` atau `http://localhost:8000/wallet`
   - Klik "Top Up"
   - Masukkan amount (min Rp 10.000)
   - Pilih payment method yang tersedia di sandbox:
     - ✅ **Credit Card** (Visa: 4811 1111 1111 1114)
     - ✅ **Virtual Account** (BCA, Mandiri, BNI, BRI)
     - ✅ **Bank Transfer**
     - ❌ **BCA KlikPay** (tidak tersedia di sandbox)

3. **Test dengan Kartu Test:**

   **✅ Kartu untuk Transaksi Berhasil:**
   - **Visa:** `4111 1111 1111 1111` (atau `4811 1111 1111 1114`)
   - **Mastercard:** `5211 1111 1111 1117`
   - **CVV:** `123`
   - **Expiry:** Bulan dan tahun mendatang (misalnya: `12/25` atau `01/2026`)
   - **OTP/3DS:** `112233` (jika diminta untuk 3D Secure)

   **❌ Kartu untuk Simulasi Gagal (Testing Error Handling):**
   - **Card Declined:** `4000 0000 0000 0002`
   - **Insufficient Funds:** `4000 0000 0000 9995`
   - **Invalid Card:** `4000 0000 0000 0127`
   - **Expired Card:** `4000 0000 0000 0069`

   **📝 Catatan:**
   - Semua kartu test ini hanya bekerja di **Sandbox Mode** (`MIDTRANS_IS_PRODUCTION=false`)
   - Tidak akan ada uang yang benar-benar ditarik dari kartu
   - Gunakan kartu dengan nomor `4111...` atau `4811...` untuk testing normal

#### 5. Verifikasi Transaksi

**Tanpa Ngrok (Skip Webhook):**
- ✅ **Status check otomatis** - Sistem akan check status ke Midtrans API saat redirect dari payment
- ✅ **Saldo update otomatis** - Saldo wallet akan ter-update langsung setelah payment success
- ✅ **Tidak perlu manual check** - Semua proses otomatis meskipun tanpa webhook
- ✅ **Sandbox Credit Card handling** - Untuk Credit Card di sandbox, sistem akan otomatis process sebagai success meskipun status masih "pending" (karena di sandbox Credit Card biasanya langsung success)
- ⚠️ **Catatan penting:**
  - Error 404 pada endpoint 3DS method-response adalah **normal di sandbox** dan tidak mempengaruhi transaksi
  - Jika popup menunjukkan "Payment successful" tapi saldo belum ter-update, sistem akan otomatis check status ke Midtrans API dan update saldo
  - Untuk Credit Card di sandbox, kadang status masih "pending" tapi sebenarnya sudah success - sistem akan handle ini otomatis

**Dengan Ngrok (Full Testing):**
- Webhook akan diterima otomatis (backup mechanism)
- Status transaksi akan update otomatis via webhook
- Saldo wallet akan ter-update setelah payment success
- Lebih reliable untuk production-like testing

#### 6. Troubleshooting Local Testing

**Issue: "Payment Notification URL tidak bisa diakses"**
- Gunakan Ngrok untuk expose local server ke internet
- Atau skip webhook untuk testing dasar (redirect tetap berfungsi)

**Issue: "Redirect tidak bekerja"**
- Pastikan `APP_URL` di `.env` sesuai dengan URL yang digunakan
- Jika pakai Herd: `APP_URL=http://noteds.test`
- Jika pakai `php artisan serve`: `APP_URL=http://localhost:8000`
- Jika pakai Ngrok: `APP_URL=https://abc123.ngrok.io`

**Issue: "BCA KlikPay error 404"**
- BCA KlikPay tidak tersedia di sandbox
- Gunakan Credit Card atau Virtual Account untuk testing

**Issue: "Webhook tidak diterima"**
- Pastikan Ngrok tunnel aktif
- Pastikan URL di Midtrans Dashboard menggunakan HTTPS (ngrok URL)
- Cek Laravel log: `tail -f storage/logs/laravel.log`

#### 7. Keuntungan Testing di Local

✅ **Lebih cepat** - tidak perlu deploy ke VPS untuk setiap perubahan
✅ **Lebih mudah debug** - bisa langsung cek log dan error
✅ **Tidak ada biaya** - sandbox gratis unlimited
✅ **Bisa test semua fitur** - dengan Ngrok, webhook juga bisa di-test

#### 8. Kapan Harus Test di VPS?

- Hanya jika perlu test dengan production keys
- Jika perlu test dengan payment method yang tidak tersedia di sandbox
- Jika perlu test dengan traffic tinggi
- Untuk final testing sebelum go-live

## 🐛 Troubleshooting

### SSL Certificate Error (NET::ERR_CERT_COMMON_NAME_INVALID)
**Problem:** Browser menampilkan error "Your connection is not private" dengan error code `NET::ERR_CERT_COMMON_NAME_INVALID`.

**Root Cause:**
- Server `noteds.test` menyajikan sertifikat SSL untuk `backend.test` (bukan `noteds.test`)
- Browser tidak dapat memverifikasi identitas server
- Ini terjadi karena Herd menggunakan sertifikat yang salah untuk domain `noteds.test`

**Solutions (Pilih salah satu):**

1. **✅ RECOMMENDED - Akses via HTTP:**
   - Akses langsung: `http://noteds.test` (bukan https://)
   - Tidak akan ada SSL certificate error karena tidak menggunakan HTTPS
   - Clear browser cache jika masih redirect ke HTTPS

2. **Disable HTTPS di Herd:**
   - Buka Laravel Herd
   - Pilih site `noteds.test`
   - Settings → Disable HTTPS / pilih HTTP Only
   - Restart site

3. **Clear HSTS Cache (Chrome/Edge) - PENTING:**
   - Buka: `chrome://net-internals/#hsts`
   - Di bagian "Delete domain security policies", masukkan: `noteds.test` dan klik "Delete"
   - Masukkan juga: `backend.test` dan klik "Delete" (untuk menghapus referensi ke domain lama)
   - Clear browser cache (Ctrl+Shift+Delete)
   - Restart browser
   - **Langkah ini CRITICAL untuk menghapus referensi ke `backend.test`**

4. **Clear Browser Cache Lengkap:**
   - Buka: `chrome://settings/clearBrowserData` (atau Ctrl+Shift+Delete)
   - Pilih: "Cached images and files", "Cookies and other site data", "Hosted app data"
   - Time range: "All time"
   - Klik "Clear data"
   - Restart browser

**Note:** Ini adalah masalah SSL certificate mismatch di Herd. Domain `backend.test` sudah tidak digunakan lagi. Pastikan untuk menghapus HSTS cache untuk kedua domain (`noteds.test` dan `backend.test`).

### Mixed Content Error (Vite + Herd HTTPS)
**Problem:** Browser console shows "Mixed Content" errors ketika mengakses `https://noteds.test`

**Root Cause:**
- Browser mengakses `https://noteds.test` (HTTPS)
- Vite dev server berjalan di `http://noteds.test:5173` (HTTP)
- Browser block mixed content (HTTPS page loading HTTP resources)

**Solutions (Pilih salah satu):**

1. **✅ RECOMMENDED - Akses via HTTP:**
   - Akses langsung: `http://noteds.test` (bukan https://)
   - Browser akan otomatis menggunakan HTTP dan tidak ada mixed content error
   - Clear browser cache jika masih redirect ke HTTPS

2. **Disable HTTPS di Herd:**
   - Buka Laravel Herd
   - Pilih site `noteds.test`
   - Settings → Disable HTTPS / pilih HTTP Only
   - Restart site

3. **Clear HSTS Cache (Chrome/Edge):**
   - Buka: `chrome://net-internals/#hsts`
   - Delete domain: `noteds.test`
   - Clear browser cache
   - Restart browser

**Note:** Production tidak terpengaruh karena menggunakan `npm run build` (assets di-build, tidak menggunakan dev server).

### Vite ERR_EMPTY_RESPONSE (Assets Not Loading)
**Problem:** Console menampilkan error `ERR_EMPTY_RESPONSE` untuk `app.css`, `app.js`, dan `client` meskipun Vite server sudah berjalan.

**Root Cause:**
- Browser mengakses via HTTPS tapi Vite server HTTP
- Hostname mismatch antara browser dan Vite server
- Network issue atau firewall block

**Solutions:**

1. **✅ Pastikan Akses via HTTP (Bukan HTTPS):**
   - Akses: `http://noteds.test` (bukan https://)
   - Vite server berjalan di HTTP port 5173
   - HTTPS tidak bisa connect ke HTTP server

2. **Check Vite Server Running:**
   ```bash
   # Windows PowerShell
   netstat -ano | findstr :5173
   ```
   - Jika ada output, berarti server sudah berjalan
   - Jika tidak ada output, jalankan: `npm run dev`

3. **Restart Vite Server:**
   ```bash
   # Stop server (Ctrl+C)
   npm run dev
   ```
   - Pastikan server start tanpa error
   - Check output: `VITE v6.x.x  ready in xxx ms`

4. **Use Production Build (Alternative):**
   Jika masih error, gunakan build:
   ```bash
   npm run build
   ```
   - Assets akan di-build ke `public/build/`
   - Tidak perlu Vite dev server
   - Reload page setelah build

5. **Check Browser Console:**
   - Buka DevTools (F12)
   - Check Network tab
   - Lihat apakah request ke `http://noteds.test:5173` berhasil atau gagal

**Status:** ✅ Fixed - Pastikan akses via HTTP dan Vite server berjalan.

### Database Connection Error
- Check MySQL service is running
- Verify `.env` database credentials
- Run `php artisan config:clear`

### Composer Install Fails
```bash
composer clear-cache
composer install --ignore-platform-reqs
```

### NPM Install Fails
```bash
npm cache clean --force
rm -rf node_modules package-lock.json
npm install
```

## 📦 Tech Stack

- **Backend:** Laravel 11 (PHP 8.2+)
- **Frontend:** Blade + Tailwind CSS + Alpine.js
- **Database:** MySQL
- **Auth:** Laravel Breeze
- **Permissions:** Spatie Permission
- **Build Tool:** Vite 6.4.1
- **AI:** Ollama (local LLM)

## 🔐 Default Credentials

**Admin:**
- Email: `admin@noteds.test`
- Password: `password`

**Seller/Buyer:**
- Created via seeders (UserSeeder)
- Email format: `seller1@noteds.test`, `buyer1@noteds.test`
- Password: `password`

## 🆕 Recent Features Added

### Note History & Versioning
- Seller bisa lihat buyer history (semua buyer yang pernah membeli note)
- Seller bisa lihat update history (timeline semua perubahan note)
- Note yang sudah dijual tidak bisa dihapus (untuk melindungi data buyer)

### AI Chat untuk Seller Profile
- **Route:** `/u/{username}/ai-chat`
- Semua user bisa bertanya tentang notes seller menggunakan AI
- AI menggunakan notes public seller sebagai context
- Akses dari: Marketplace, Profile seller, atau langsung via URL

### Collections Enhancement
- Tombol "Add Purchased Notes" di collection
- Dropdown untuk memilih purchased notes yang belum ada di collection
- Hanya purchased notes yang bisa ditambahkan

### Resell Flow
- One-time sale: Buyer yang sudah menjual note tidak bisa akses lagi
- Original creator selalu dapat komisi di setiap resell
- Warning messages sebelum dan setelah menjual

### Profile Features
- Avatar upload (file atau URL)
- Share functionality (Facebook, Twitter, WhatsApp, LinkedIn, Copy Link)
- Open Graph & Twitter Card meta tags

## 📝 Next Steps

1. Read [TASKLIST.md](TASKLIST.md) for development roadmap
2. Read [README.md](README.md) for platform overview
3. Read [VPS_SETUP.md](VPS_SETUP.md) for deployment guide

