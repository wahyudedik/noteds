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

### 6. Frontend Assets

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

### 7. Development Tools

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

**Note:** This command is automatically scheduled to run daily at 00:00 WIB in production (see `routes/console.php`). In development, you can run it manually to test subscription renewal logic.

**What it does:**
- Checks active premium subscriptions expiring today or tomorrow
- Auto-renews if wallet balance is sufficient
- Expires subscription and sends notifications if balance is insufficient
- See [VPS_SETUP.md](VPS_SETUP.md) for detailed documentation

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

## 📝 Next Steps

1. Read [TASKLIST.md](TASKLIST.md) for development roadmap
2. Read [README.md](README.md) for platform overview
3. Read [VPS_SETUP.md](VPS_SETUP.md) for deployment guide

