# 🌐 Noteds — VPS Deployment Guide

## 📋 Server Requirements

- Ubuntu 22.04 LTS (recommended)
- Nginx
- PHP 8.2+ FPM
- MySQL 8.0+
- Node.js 18+ / NPM
- Composer 2.x
- Supervisor (for queue workers)
- Redis (recommended, for caching - auto-detect, fallback to database)
- Ollama (for AI features - can be installed on same server or separate)
- PHP GD or Imagick extension (for image processing - optional)
- Fail2ban (for SSH protection - recommended)
- Certbot (for SSL certificates - Let's Encrypt)

## 🚀 Deployment Steps

### 1. Server Preparation

```bash
# Update system
sudo apt update && sudo apt upgrade -y 

# Install PHP 8.2
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2-fpm php8.2-cli php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl php8.2-redis -y

# Install Redis (for caching)
sudo apt install redis-server -y
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Install Nginx
sudo apt install nginx -y

# Install MySQL
sudo apt install mysql-server -y

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install additional PHP extensions for Laravel 12
sudo apt install php8.2-readline php8.2-tokenizer php8.2-fileinfo -y

# Install Supervisor (for queue workers)
sudo apt install supervisor -y

# Install Fail2ban (for SSH protection)
sudo apt install fail2ban -y

# Install Certbot (for SSL certificates)
sudo apt install certbot python3-certbot-nginx -y
```

### 2. Database Setup

```bash
sudo mysql
```

```sql
CREATE DATABASE noteds_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'noteds_user'@'localhost' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON noteds_production.* TO 'noteds_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Application Deployment

```bash
# Clone repository
cd /var/www
sudo git clone <repository-url> noteds
sudo chown -R www-data:www-data noteds
cd noteds

# Install dependencies
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data npm install
sudo -u www-data npm run build

# Environment setup
sudo -u www-data cp .env.example .env
sudo -u www-data php artisan key:generate

# Set proper permissions for .env
sudo chmod 600 /var/www/noteds/.env
sudo chown www-data:www-data /var/www/noteds/.env
```

### 4. Environment Configuration

Edit `.env`:
```env
APP_NAME="Noteds"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=noteds_production
DB_USERNAME=noteds_user
DB_PASSWORD=your_strong_password

# Midtrans Configuration (akan dijelaskan di section 5)
# MIDTRANS_SERVER_KEY=your_production_key
# MIDTRANS_CLIENT_KEY=your_production_key
# MIDTRANS_IS_PRODUCTION=true

# Ollama Configuration (for AI features - CPU Optimized)
OLLAMA_URL=http://localhost:11434
OLLAMA_MODEL=llama3.2
OLLAMA_NUM_THREADS=8      # Sesuaikan dengan jumlah CPU cores (auto-detect jika null)
OLLAMA_NUM_CTX=4096       # Context window (4096 = 4K tokens, 8192 = 8K tokens)
OLLAMA_BATCH_SIZE=512     # Batch size untuk CPU inference
OLLAMA_TIMEOUT=120        # Timeout dalam detik (120 = 2 menit untuk CPU)
OLLAMA_USE_MLOCK=false    # Lock memory (perlu root, untuk performa lebih baik)
OLLAMA_NUMA=false         # NUMA optimization (untuk multi-socket CPU)
# Or if Ollama is on different server:
# OLLAMA_URL=http://your-ollama-server:11434

QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=redis  # Auto-detect Redis if REDIS_HOST is set, fallback to database
# Redis Configuration (Optional but Recommended)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CLIENT=phpredis
REDIS_CACHE_DB=1

# CDN Configuration (Optional)
CDN_URL=https://cdn.yourdomain.com  # If not set, will use APP_URL

# Telescope Query Monitoring
TELESCOPE_ENABLED=true
TELESCOPE_QUERY_WATCHER=true
TELESCOPE_SLOW_QUERY_THRESHOLD=100  # milliseconds

# Mail Configuration (for notifications & contact form)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mail_username
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# Support Email (for contact form - optional, defaults to MAIL_FROM_ADDRESS)
# Can be configured via Admin Settings UI

# Security Configuration
SECURITY_CSP_ENABLED=true
SECURITY_SANITIZE_INPUT=true
FILE_UPLOAD_MAX_SIZE=10485760  # 10MB in bytes
FILE_UPLOAD_VALIDATE_MIME=true
FILE_UPLOAD_VALIDATE_MAGIC=true

# Rate Limiting Configuration (for sensitive endpoints)
RATE_LIMIT_PURCHASE=5
RATE_LIMIT_WALLET_TOPUP=10
RATE_LIMIT_WITHDRAW=3
RATE_LIMIT_RESALE=5
RATE_LIMIT_ESCROW=5
RATE_LIMIT_QUOTE=5
```

> Pastikan queue (`QUEUE_CONNECTION=database`) dan mailer sudah dikonfigurasi agar notifikasi forum & email berjalan lancar.

### 5. Midtrans Payment Gateway Setup

Midtrans adalah payment gateway yang digunakan untuk memproses transaksi pembayaran (premium subscription, pembelian notes, dll).

#### 5.1. Daftar & Setup Akun Midtrans

1. **Daftar Akun Midtrans:**
   - Kunjungi [https://dashboard.midtrans.com](https://dashboard.midtrans.com)
   - Daftar akun baru atau login jika sudah punya
   - Pilih plan sesuai kebutuhan (dapat mulai dengan free plan untuk testing)

2. **Dapatkan API Keys:**
   - Login ke Midtrans Dashboard
   - Navigate ke **Settings** → **Access Keys**
   - Anda akan melihat:
     - **Server Key** (untuk backend)
     - **Client Key** (untuk frontend)
   - **PENTING:** Ada 2 environment:
     - **Sandbox** (untuk testing) - gratis, unlimited
     - **Production** (untuk live) - perlu verifikasi

3. **Untuk Development/Testing:**
   - Gunakan **Sandbox Keys** dari dashboard
   - Set `MIDTRANS_IS_PRODUCTION=false` di `.env`

4. **Untuk Production:**
   - **Verifikasi Akun Midtrans:**
     - Login ke [Midtrans Dashboard](https://dashboard.midtrans.com)
     - Navigate ke **Settings** → **Business Information**
     - Upload dokumen bisnis yang diperlukan:
       - KTP/Paspor (untuk individu) atau NPWP (untuk perusahaan)
       - Dokumen legal bisnis (jika perusahaan)
       - Informasi bank untuk settlement
     - Tunggu verifikasi dari tim Midtrans (biasanya 1-3 hari kerja)
   - **Setelah Verifikasi:**
     - Login ke Dashboard → **Settings** → **Access Keys**
     - **PENTING:** Switch ke **Production** tab (bukan Sandbox)
     - Copy **Production Server Key** (dimulai dengan `Mid-server-`)
     - Copy **Production Client Key** (dimulai dengan `Mid-client-`)
     - Copy **Merchant ID** (jika ada)
   - **Update `.env` di Production Server:**
     ```env
     MIDTRANS_SERVER_KEY=Mid-server-xxxxx  # Production Server Key
     MIDTRANS_CLIENT_KEY=Mid-client-xxxxx   # Production Client Key
     MIDTRANS_IS_PRODUCTION=true            # CRITICAL: Must be true
     MIDTRANS_MERCHANT_ID=Gxxxxx           # Production Merchant ID
     ```
   - **Clear Laravel Cache:**
     ```bash
     php artisan config:clear
     php artisan cache:clear
     php artisan optimize
     ```
   - **Verifikasi Konfigurasi:**
     - Pastikan `APP_URL` menggunakan HTTPS: `https://your-domain.com`
     - Pastikan semua URL endpoints sudah dikonfigurasi di Midtrans Dashboard
     - Test dengan transaksi kecil pertama kali

#### 5.2. Konfigurasi di Aplikasi

**Update `.env` dengan keys dari Midtrans Dashboard:**

```env
# Midtrans Configuration
MIDTRANS_SERVER_KEY=your_server_key_here
MIDTRANS_CLIENT_KEY=your_client_key_here
MIDTRANS_IS_PRODUCTION=false  # true untuk production, false untuk sandbox
MIDTRANS_MERCHANT_ID=your_merchant_id  # Optional, jika ada
```

**Catatan:**
- `MIDTRANS_SERVER_KEY`: Digunakan di backend untuk membuat Snap Token dan handle webhook
- `MIDTRANS_CLIENT_KEY`: Digunakan di frontend untuk memuat Snap.js
- `MIDTRANS_IS_PRODUCTION`: Harus sesuai dengan environment keys yang digunakan
- Jangan pernah commit keys ke repository!

#### 5.3. Verifikasi Konfigurasi

Setelah setup, aplikasi akan otomatis menggunakan Midtrans melalui:
- `config/services.php` - membaca dari `.env`
- `WalletController` - menggunakan Midtrans untuk Snap Token
- Webhook handler untuk update status transaksi

**Test Payment Flow:**
1. Coba beli note atau subscribe premium
2. Pastikan payment page muncul dengan Snap.js
3. Gunakan kartu test dari Midtrans untuk testing
4. Cek webhook notification di Midtrans Dashboard

**Midtrans Test Cards:**
- Visa: 4811 1111 1111 1114
- Mastercard: 5211 1111 1111 1117
- CVV: 123
- Expiry: Any future date

**Midtrans URL Endpoints Configuration:**

Setelah deploy ke production, Anda HARUS mengkonfigurasi URL endpoints berikut di Midtrans Dashboard:

1. **Login ke Midtrans Dashboard:**
   - Kunjungi [https://dashboard.midtrans.com](https://dashboard.midtrans.com)
   - Login dengan akun production Anda

2. **Navigate ke Settings → Configuration → URL Settings**

3. **Konfigurasi URL Endpoints:**

   **Payment Notification URL** (Webhook - Required):
   ```
   https://your-domain.com/payment/callback
   ```
   atau
   ```
   https://your-domain.com/wallet/webhook
   ```
   - Address where Midtrans will send payment notification via HTTP POST
   - Route handler: `WalletController::webhook()` atau `WalletController::paymentCallback()`
   - **PENTING:** Must use HTTPS in production
   - **PENTING:** Route is exempt from CSRF protection (required for Midtrans)
   - Automatically updates transaction status after payment completion
   - Handles duplicate notifications with idempotency check

   **Recurring Notification URL** (Optional - for recurring payments):
   ```
   https://your-domain.com/payment/callback
   ```
   - Address for recurring payment notifications
   - Same handler as payment notification URL

   **Pay Account Notification URL** (Optional - for pay account status):
   ```
   https://your-domain.com/payment/callback
   ```
   - Address for pay account status notifications
   - Same handler as payment notification URL

   **Finish Redirect URL** (Required):
   ```
   https://your-domain.com/payment/finish
   ```
   - Customer redirected here after successful payment
   - Route handler: `WalletController::paymentFinish()`
   - Shows success message and redirects to wallet page

   **Unfinish Redirect URL** (Required):
   ```
   https://your-domain.com/payment/unfinish
   ```
   - Customer redirected here if they click "Back to Order Website" on payment page
   - Route handler: `WalletController::paymentUnfinish()`
   - Shows info message about incomplete payment

   **Error Redirect URL** (Required):
   ```
   https://your-domain.com/payment/error
   ```
   - Customer redirected here if payment fails or encounters error
   - Route handler: `WalletController::paymentError()`
   - Shows error message and allows user to retry

4. **Save Configuration:**
   - Click "Save" after entering all URLs
   - Midtrans will validate URLs (must be accessible via HTTPS)
   - URLs must return proper HTTP status codes (200 OK)

5. **Testing Endpoints:**
   - Test webhook: `curl -X POST https://your-domain.com/payment/callback`
   - Test finish: Visit `https://your-domain.com/payment/finish?order_id=test`
   - Test unfinish: Visit `https://your-domain.com/payment/unfinish?order_id=test`
   - Test error: Visit `https://your-domain.com/payment/error?order_id=test`

**Webhook Security Notes:**
- Webhook route is exempt from CSRF protection (required for Midtrans)
- All webhook routes use POST method (except redirect URLs which are GET)
- Webhook handler validates transaction status and prevents duplicate processing
- Amount verification is performed for security
- All webhook calls are logged for debugging
- Duplicate webhook calls are handled with idempotency check
- **PENTING:** Webhook must be accessible via HTTPS in production
- **PENTING:** Never expose webhook endpoint in public repositories

### 6. Run Migrations & Seeders

```bash
# Run database migrations
sudo -u www-data php artisan migrate --force

# Seed database dengan initial data (admin user, dll)
sudo -u www-data php artisan db:seed --force
```
> Seeder default akan mengisi tax rules dasar, commission tiers (Starter/Growth/Pro), price guidance settings, dan halaman legal wajib. Jika perlu memperbarui subset data saja, jalankan seeder per kelas:  
> `sudo -u www-data php artisan db:seed --class=TaxRuleSeeder --force` atau `CommissionTierSeeder`, dll.

**Catatan Migrations:**
- Pastikan semua migrations berhasil dijalankan
- Jika ada error, cek database connection di `.env`
- Untuk production, backup database sebelum migrate

### 7. Storage & Permissions

```bash
# Create storage link
sudo -u www-data php artisan storage:link

# Set proper permissions for storage
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache

# Set proper permissions for application
sudo chmod -R 755 /var/www/noteds
sudo chmod -R 775 /var/www/noteds/storage
sudo chmod -R 775 /var/www/noteds/bootstrap/cache

# Set secure permissions for private storage (KYC documents)
sudo chmod -R 750 /var/www/noteds/storage/app/private
sudo chown -R www-data:www-data /var/www/noteds/storage/app/private

# Set secure permissions for public storage (user uploads)
sudo chmod -R 775 /var/www/noteds/storage/app/public
sudo chown -R www-data:www-data /var/www/noteds/storage/app/public

# Set secure permissions for .env file
sudo chmod 600 /var/www/noteds/.env
sudo chown www-data:www-data /var/www/noteds/.env
```

> Identity verification documents (KTP & selfie) are stored on the `private` disk (`storage/app/private`), downloaded via secured admin routes. No public exposure required.

### 8. Optimize Laravel

```bash
# For Laravel 12, use optimize command (combines all caches)
sudo -u www-data php artisan optimize

# Or manually cache each component:
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

### 9. Nginx Configuration

Create `/etc/nginx/sites-available/noteds`:
```nginx
# HTTP to HTTPS redirect (will be updated by certbot)
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/noteds/public;

    # Temporary headers (will be enhanced after SSL)
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Disable PHP execution in storage directories (security)
    location ~ ^/storage/.*\.php$ {
        deny all;
    }

    # Disable PHP execution in upload directories (security)
    location ~ ^/public/uploads/.*\.php$ {
        deny all;
    }

    # Increase upload size for file attachments (support up to 200MB for large files)
    # For premium users: up to 100MB per file, multiple files can exceed 200MB total
    client_max_body_size 200M;
    
    # Increase buffer sizes for large file uploads
    client_body_buffer_size 128k;
    client_header_buffer_size 1k;
    large_client_header_buffers 4 16k;
    
    # Increase timeouts for large file uploads
    client_body_timeout 300s;
    client_header_timeout 300s;
    send_timeout 300s;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss;
    gzip_comp_level 6;
    gzip_disable "msie6";
}
```

**Note:** Setelah menjalankan `certbot --nginx`, konfigurasi akan otomatis di-update dengan:
- SSL certificate paths
- HTTPS server block
- HTTP to HTTPS redirect
- Enhanced security headers (HSTS, dll)

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/noteds /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 10. SSL Certificate (Let's Encrypt)

**Before running certbot, ensure:**
- Domain DNS points to your server IP
- Port 80 and 443 are open in firewall
- Nginx configuration is correct

```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d your-domain.com
```

**During certbot setup:**
- Enter email for renewal notifications
- Agree to terms of service
- Choose whether to redirect HTTP to HTTPS (recommended: Yes)

**Auto-renewal Setup:**
```bash
# Test renewal
sudo certbot renew --dry-run

# Certbot automatically sets up renewal cronjob
# Check with: sudo systemctl status certbot.timer
```

### 10.1. Security Hardening Setup

#### 10.1.1. Security Headers Configuration

Security headers sudah di-handle oleh `SecurityHeaders` middleware, tapi untuk performa lebih baik, bisa juga ditambahkan di Nginx:

Update `/etc/nginx/sites-available/noteds` setelah SSL setup:
```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /var/www/noteds/public;

    # SSL Configuration (Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers (additional layer, middleware juga handle)
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;

    # Remove X-Powered-By (handled by middleware, but also here)
    fastcgi_hide_header X-Powered-By;

    # ... rest of configuration
}

# HTTP to HTTPS redirect
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}
```

#### 10.1.2. Fail2Ban Setup (SSH Protection)

```bash
# Install fail2ban
sudo apt install fail2ban -y

# Create local configuration
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local

# Edit configuration
sudo nano /etc/fail2ban/jail.local
```

Add/modify in `jail.local`:
```ini
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[sshd]
enabled = true
port = 22
logpath = /var/log/auth.log
maxretry = 3
bantime = 7200
```

Start fail2ban:
```bash
sudo systemctl enable fail2ban
sudo systemctl start fail2ban

# Check status
sudo fail2ban-client status
sudo fail2ban-client status sshd
```

#### 10.1.3. File Upload Security

File upload security sudah di-handle oleh `FileUploadSecurityService`. Pastikan:

1. **Storage Permissions:**
```bash
# Private storage untuk KYC documents
sudo chmod -R 750 /var/www/noteds/storage/app/private
sudo chown -R www-data:www-data /var/www/noteds/storage/app/private

# Public storage untuk user uploads
sudo chmod -R 775 /var/www/noteds/storage/app/public
sudo chown -R www-data:www-data /var/www/noteds/storage/app/public
```

2. **Disable PHP Execution in Upload Directories:**
Add to Nginx config:
```nginx
# Disable PHP execution in storage directories
location ~ ^/storage/.*\.php$ {
    deny all;
}

# Disable PHP execution in public/uploads
location ~ ^/public/uploads/.*\.php$ {
    deny all;
}
```

#### 10.1.4. Rate Limiting Verification

Rate limiting sudah dikonfigurasi di routes. Untuk verifikasi:

```bash
# Test rate limiting (should return 429 after limit)
for i in {1..6}; do
  curl -X POST https://your-domain.com/marketplace/{note}/purchase \
    -H "Cookie: laravel_session=..." \
    -H "X-CSRF-TOKEN=..."
done
```

#### 10.1.5. Security Logging Setup

Security events sudah di-log otomatis. Untuk monitoring:

```bash
# Monitor security events
sudo tail -f /var/www/noteds/storage/logs/laravel.log | grep -i "security\|upload\|rate.*limit\|mime"

# Setup log rotation (optional)
sudo nano /etc/logrotate.d/noteds
```

Add to `/etc/logrotate.d/noteds`:
```
/var/www/noteds/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        /usr/bin/supervisorctl restart noteds-worker:* > /dev/null 2>&1 || true
    endscript
}
```

### 11. Queue Workers (Supervisor)

Create `/etc/supervisor/conf.d/noteds-worker.conf`:
```ini
[program:noteds-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/noteds/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/noteds/storage/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start noteds-worker:*
```

> Queue workers memproses email notifikasi forum, job AI (`ProcessAiRequest`), serta tugas-tugas background lain. Pastikan service ini selalu aktif.

**AI Request Queuing:**
- AI requests dapat diproses secara asinkron menggunakan `ProcessAiRequest` job untuk menghindari blocking pada high traffic
- Job ini menangani: analyze, ask, generate_image, generate_video, search_images, semantic_search, context_links, generate_content, generate_ideas
- Retry mechanism: 3 attempts dengan backoff 5 detik
- Performance tracking: Duration logging untuk monitoring
- Caching: Hasil dapat di-cache untuk mempercepat response

### 12. Scheduled Tasks (Cron)

Edit crontab:
```bash
sudo crontab -e -u www-data
```

Add:
```cron
* * * * * cd /var/www/noteds && php artisan schedule:run >> /dev/null 2>&1
```

**Note:** Laravel scheduler (`schedule:run`) will automatically run all scheduled tasks defined in `routes/console.php`, including:
- Subscription auto-renewal (`subscriptions:renew`) - runs daily at 00:00 WIB
- Featured notes expiry check (`featured:expire`) - runs daily at 01:00 WIB
- Publish scheduled forum posts (`forum:publish-scheduled-posts`) - runs every minute

#### Subscription Auto-Renewal Command

The `php artisan subscriptions:renew` command is automatically scheduled to run daily at 00:00 WIB (configured in `routes/console.php`).

**What it does:**
- Checks all active premium subscriptions that are expiring today or tomorrow
- **If wallet balance is sufficient:**
  - Automatically deducts premium price from user's wallet
  - Extends subscription for another month
  - Creates transaction record
  - Sends app notification to user
- **If wallet balance is insufficient:**
  - Changes subscription status to `expired`
  - Sends email notification to user about renewal failure
  - Sends app notification
  - Premium features are automatically disabled (via `EnsureUserHasPremium` middleware)

**Manual execution (for testing):**
```bash
sudo -u www-data php artisan subscriptions:renew
```

**Monitoring:**
- Check Laravel logs: `tail -f storage/logs/laravel.log`
- Command output includes summary: renewed count, expired count, errors
- All errors are logged for debugging

**Important Notes:**
- Command uses database transactions to ensure data consistency
- Premium price is retrieved from Settings table (`premium_price_monthly` key)
- Email notifications are sent via Laravel Mail system (configured in `.env`)
- App notifications are stored in `notifications` table
- Subscription expiration automatically disables premium features via middleware

#### Featured Notes Auto-Expiry Command

The `php artisan featured:expire` command is automatically scheduled to run daily at 01:00 WIB (configured in `routes/console.php`).

**What it does:**
- Checks all active featured notes that have passed their `end_date`
- Automatically updates status from `active` to `expired`
- Prevents expired featured notes from being displayed
- Logs all expired featured notes for admin review

**Manual execution (for testing):**
```bash
sudo -u www-data php artisan featured:expire
```

**Monitoring:**
- Check Laravel logs: `tail -f storage/logs/laravel.log`
- Command output includes summary: total expired count, errors
- All errors are logged for debugging

**Important Notes:**
- Command processes all featured notes with `status = 'active'` and `end_date < now()`
- Featured notes are automatically hidden from display after expiry
- Expired featured notes can be extended with a new payment request
- Analytics data (impressions, clicks) are preserved after expiry

#### Publish Scheduled Forum Posts

The `php artisan forum:publish-scheduled-posts` command is scheduled to run every minute.

**What it does:**
- Checks forum posts with `is_published = false` and `scheduled_at <= now()`
- Activates the post (sets `is_published=true`, clears `scheduled_at`, stamps `published_at`)
- Ensures scheduled posts appear automatically without manual intervention

**Manual execution (for testing):**
```bash
sudo -u www-data php artisan forum:publish-scheduled-posts
```

**Monitoring:**
- Check Laravel logs: `tail -f storage/logs/laravel.log | grep publish-scheduled-posts`
- Command output includes count of posts published

**Important Notes:**
- Requires queue/cron to run reliably (see steps 11 & 12)
- Owners see scheduled indicators until publish time is reached

### 13. Ollama Setup (AI Features) - CPU Optimized

Ollama can be installed on the same server or a separate server. **This setup is optimized for CPU-only inference (no GPU required).**

#### Option 1: Install Ollama on Same Server (Recommended for VPS)

```bash
# Install Ollama
curl -fsSL https://ollama.com/install.sh | sh

# Start Ollama service
sudo systemctl enable ollama
sudo systemctl start ollama

# Pull required model (adjust based on your needs)
# For CPU-only: Use smaller models for better performance
ollama pull llama3.2        # ~2GB, good for CPU
# or
ollama pull mistral:7b      # ~4GB, better quality
# or
ollama pull qwen2.5:7b      # ~4.5GB, good balance

# For better CPU performance, use quantized models:
ollama pull llama3.2:3b     # ~2GB, faster on CPU
```

#### Option 2: Install Ollama on Separate Server
If using a separate server, ensure:
- Ollama service is running on that server
- Update `.env` with correct `OLLAMA_URL`
- Configure firewall to allow connection (if needed)

#### CPU Optimization Configuration

**PENTING:** Konfigurasi Ollama sudah termasuk di bagian **Environment Configuration** (step 4) di atas. Pastikan semua variabel berikut ada di file `.env`:

```env
# Ollama Configuration (for AI features - CPU Optimized)
OLLAMA_URL=http://localhost:11434
OLLAMA_MODEL=llama3.2
OLLAMA_NUM_THREADS=8      # Sesuaikan dengan jumlah CPU cores (auto-detect jika null)
OLLAMA_NUM_CTX=4096       # Context window (4096 = 4K tokens, 8192 = 8K tokens)
OLLAMA_BATCH_SIZE=512     # Batch size untuk CPU inference
OLLAMA_TIMEOUT=120        # Timeout dalam detik (120 = 2 menit untuk CPU)
OLLAMA_USE_MLOCK=false    # Lock memory (perlu root, untuk performa lebih baik)
OLLAMA_NUMA=false         # NUMA optimization (untuk multi-socket CPU)
```

**Catatan:**
- Jika `OLLAMA_NUM_THREADS` tidak di-set atau di-set `null`, sistem akan auto-detect jumlah CPU cores
- Setelah update `.env`, jalankan: `php artisan config:clear && php artisan cache:clear`

#### CPU Optimization Features

The AI service automatically:
- **Auto-detects CPU cores** and uses all available cores (minus 1 for system)
- **Disables GPU** (sets `num_gpu=0`) for CPU-only inference
- **Optimizes memory usage** with memory mapping (`use_mmap=true`)
- **Uses optimal batch size** for CPU inference
- **Configures thread count** based on available CPU cores

#### Verify Ollama Connection
```bash
curl http://localhost:11434/api/tags
```

#### Performance Tuning for CPU

1. **For VPS with 4-8 CPU cores:**
   ```env
   OLLAMA_NUM_THREADS=6        # Leave 1-2 cores for system (atau biarkan null untuk auto-detect)
   OLLAMA_NUM_CTX=4096         # 4K context window
   OLLAMA_BATCH_SIZE=512       # Smaller batch for CPU
   OLLAMA_TIMEOUT=120          # 2 menit timeout
   ```

2. **For VPS with 8+ CPU cores:**
   ```env
   OLLAMA_NUM_THREADS=12       # Use more threads (atau biarkan null untuk auto-detect)
   OLLAMA_NUM_CTX=8192         # 8K context window (if RAM allows)
   OLLAMA_BATCH_SIZE=1024      # Larger batch
   OLLAMA_TIMEOUT=180          # 3 menit timeout untuk request yang lebih kompleks
   ```

3. **For better performance (requires root):**
   ```env
   OLLAMA_USE_MLOCK=true       # Lock memory (prevents swapping)
   OLLAMA_NUMA=true            # NUMA optimization (untuk multi-socket CPU)
   ```

4. **For VPS with limited memory (< 4GB):**
   ```env
   OLLAMA_NUM_CTX=2048         # Reduce context window untuk menghemat memory
   OLLAMA_BATCH_SIZE=256       # Smaller batch size
   OLLAMA_TIMEOUT=180          # Increase timeout karena mungkin lebih lambat
   ```

#### Monitor CPU Usage
```bash
# Check CPU usage during AI inference
htop
# or
top

# Check Ollama process
ps aux | grep ollama

# Monitor Ollama logs
sudo journalctl -u ollama -f
```

#### AI Error Handling & Monitoring

**Error Handling Improvements:**
- ✅ **Stability AI API:** Enhanced error handling dengan retry mechanism (2x dengan delay 2s), validation untuk size/dimensions, file verification, detailed error logging untuk HTTP status codes (401, 402, 403, 429, 500+)
- ✅ **Unsplash API:** Retry mechanism (2x dengan delay 100ms), validation untuk photo data, specific error handling untuk HTTP status codes, connection exception handling
- ✅ **RunwayML API:** Retry mechanism (2x dengan delay 2s), validation untuk duration/ratio, response structure validation, detailed error logging
- ✅ **Ollama API:** Enhanced error handling dengan null checks, fallback values, detailed logging dengan trace

**Monitoring:**
- All AI requests are logged with duration tracking
- Error logs include: status codes, error bodies, traces, user IDs, prompts (truncated)
- Performance metrics: Request duration, success/failure rates, retry counts
- Log location: `storage/logs/laravel.log`

**Check AI Service Status:**
```bash
# View AI-related logs
tail -f /var/www/noteds/storage/logs/laravel.log | grep -i "ai\|ollama\|stability\|unsplash\|runway"

# Check AI service availability
curl http://localhost:11434/api/tags

# Monitor queue jobs for AI requests
php artisan queue:work --queue=default
```

**API Configuration:**
Add to `.env` for external AI APIs (optional):
```env
# Stability AI (for image generation)
STABILITY_API_KEY=your_stability_api_key

# Unsplash (for image search)
UNSPLASH_ACCESS_KEY=your_unsplash_access_key

# RunwayML (for video generation)
RUNWAY_API_KEY=your_runway_api_key
```

### 14. Monitoring Setup

#### Laravel Telescope (Production Admin Only)
Already configured in `TelescopeServiceProvider` with admin role gate.

**Access Telescope:**
- URL: `https://your-domain.com/telescope`
- Only accessible by admin users (gate: `viewTelescope`)
- Monitor queries, requests, jobs, exceptions, etc.

**Telescope Configuration:**
```env
TELESCOPE_ENABLED=true
TELESCOPE_QUERY_WATCHER=true
TELESCOPE_SLOW_QUERY_THRESHOLD=100  # milliseconds
TELESCOPE_MAIL_WATCHER=true
TELESCOPE_MODEL_WATCHER=true
TELESCOPE_REDIS_WATCHER=true
TELESCOPE_REQUEST_WATCHER=true
```

#### Sentry (Error Tracking - Optional)
```bash
composer require sentry/sentry-laravel
```

Configure in `.env`:
```env
SENTRY_LARAVEL_DSN=your_sentry_dsn
SENTRY_TRACES_SAMPLE_RATE=1.0
SENTRY_ENVIRONMENT=production
```

#### Log Monitoring
```bash
# View Laravel logs
sudo tail -f /var/www/noteds/storage/logs/laravel.log

# View Nginx access logs
sudo tail -f /var/log/nginx/access.log

# View Nginx error logs
sudo tail -f /var/log/nginx/error.log

# View security-related logs
sudo tail -f /var/www/noteds/storage/logs/laravel.log | grep -i "security\|upload\|rate.*limit\|mime\|sanitize"

# View queue worker logs
sudo tail -f /var/www/noteds/storage/logs/worker.log
```

#### System Resource Monitoring

**Install monitoring tools:**
```bash
# htop for process monitoring
sudo apt install htop -y

# iotop for I/O monitoring
sudo apt install iotop -y

# nethogs for network monitoring
sudo apt install nethogs -y
```

**Monitor system resources:**
```bash
# CPU and memory usage
htop

# Disk usage
df -h
du -sh /var/www/noteds/storage/*

# Network connections
netstat -tulpn | grep :80
netstat -tulpn | grep :443

# PHP-FPM processes
ps aux | grep php-fpm

# Queue workers
ps aux | grep "queue:work"
```

#### Automated Health Checks

Create health check script `/usr/local/bin/noteds-health-check.sh`:
```bash
#!/bin/bash

# Check Laravel application
if ! curl -f -s https://your-domain.com/up > /dev/null; then
    echo "ERROR: Application health check failed"
    exit 1
fi

# Check database connection
if ! php /var/www/noteds/artisan db:show > /dev/null 2>&1; then
    echo "ERROR: Database connection failed"
    exit 1
fi

# Check queue workers
if ! supervisorctl status noteds-worker:* | grep -q RUNNING; then
    echo "ERROR: Queue workers not running"
    exit 1
fi

# Check Redis (if enabled)
if [ ! -z "$REDIS_HOST" ]; then
    if ! redis-cli -h $REDIS_HOST ping > /dev/null 2>&1; then
        echo "ERROR: Redis connection failed"
        exit 1
    fi
fi

echo "OK: All health checks passed"
exit 0
```

Make executable:
```bash
sudo chmod +x /usr/local/bin/noteds-health-check.sh
```

Add to crontab for periodic checks:
```bash
sudo crontab -e
# Add: */5 * * * * /usr/local/bin/noteds-health-check.sh || echo "Health check failed" | mail -s "Noteds Health Alert" admin@your-domain.com
```

## 🔄 Deployment Workflow

### Manual Deployment
```bash
cd /var/www/noteds
sudo git pull origin main
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data php artisan migrate --force
# Optional: re-seed business rules (tax/commission) if there were changes in config
# sudo -u www-data php artisan db:seed --class=CommissionTierSeeder --force
sudo -u www-data npm run build
sudo -u www-data php artisan optimize  # Laravel 12: combines config, route, view cache
sudo supervisorctl restart noteds-worker:*
```

### Automated Deployment (GitHub Actions)

**Create `.github/workflows/deploy.yml`:**
```yaml
name: Deploy to Production

on:
  push:
    branches: [ main ]
  workflow_dispatch:

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - name: Deploy to server
      uses: appleboy/ssh-action@master
      with:
        host: ${{ secrets.HOST }}
        username: ${{ secrets.USERNAME }}
        key: ${{ secrets.SSH_KEY }}
        script: |
          cd /var/www/noteds
          git pull origin main
          sudo -u www-data composer install --no-dev --optimize-autoloader
          sudo -u www-data php artisan migrate --force
          sudo -u www-data npm run build
          sudo -u www-data php artisan optimize
          sudo supervisorctl restart noteds-worker:*
```

**GitHub Secrets Required:**
- `HOST`: Your VPS IP address
- `USERNAME`: SSH username (usually `root` or `ubuntu`)
- `SSH_KEY`: Private SSH key for authentication

### Deployment Checklist

**Before Deployment:**
- [ ] Review changes in git log
- [ ] Test changes in staging environment (if available)
- [ ] Backup database: `/usr/local/bin/noteds-backup.sh`
- [ ] Check disk space: `df -h`
- [ ] Verify queue workers are running: `supervisorctl status`
- [ ] Check application health: `curl https://your-domain.com/up`

**During Deployment:**
- [ ] Pull latest code
- [ ] Install/update dependencies
- [ ] Run migrations (with `--force` flag)
- [ ] Build frontend assets (`npm run build`)
- [ ] Clear and optimize caches
- [ ] Restart queue workers
- [ ] Verify application is running

**After Deployment:**
- [ ] Test critical paths (login, purchase, upload)
- [ ] Check error logs: `tail -f storage/logs/laravel.log`
- [ ] Verify queue processing: `supervisorctl status`
- [ ] Monitor resource usage: `htop`
- [ ] Check Telescope for errors: `/telescope`
- [ ] Verify scheduled tasks are running: Check cron logs

## 🔒 Security Checklist

- [ ] Set `APP_DEBUG=false`
- [ ] Use strong database passwords
- [ ] Enable SSL/HTTPS
- [ ] Configure firewall (UFW)
- [ ] Set proper file permissions (775/664)
- [ ] Disable PHP execution in storage/public directories
- [ ] Regular security updates (`sudo apt update && sudo apt upgrade`)
- [ ] Database backups automated
- [ ] Rate limiting configured (Laravel throttling + custom middleware)
- [ ] Email notification for errors
- [ ] Hide PHP version (X-Powered-By header) - handled by SecurityHeaders middleware
- [ ] Secure `.env` file (chmod 600)
- [ ] Restrict MySQL user privileges
- [ ] Configure fail2ban for SSH protection
- [ ] Regular security audits
- [ ] Webhook CSRF exemption properly configured (only for Midtrans webhook)
- [ ] Midtrans API keys stored securely in `.env` (never commit to repository)
- [ ] Webhook endpoint accessible only via HTTPS in production
- [ ] Duplicate transaction processing prevention enabled
- [ ] Featured notes auto-expire command scheduled (daily at 01:00 WIB)
- [ ] Featured notes pricing configured in admin settings
- [ ] Featured notes analytics tracking enabled
- [ ] Security headers enabled (CSP, HSTS, X-Frame-Options) - handled by SecurityHeaders middleware
- [ ] Input sanitization enabled - handled by SanitizeInput middleware
- [ ] File upload security validation enabled - handled by FileUploadSecurityService
- [ ] Rate limiting on sensitive endpoints (purchase, wallet, withdraw) - configured in routes
- [ ] Security logging enabled for monitoring suspicious activities
- [ ] Review [SECURITY.md](SECURITY.md) for complete security guide
- [ ] Fail2ban configured for SSH protection
- [ ] Security headers configured in Nginx (additional layer)
- [ ] PHP execution disabled in upload directories
- [ ] Log rotation configured for Laravel logs
- [ ] Automated backups configured (database + storage)
- [ ] Health check monitoring setup
- [ ] SSL certificate auto-renewal verified
- [ ] Firewall (UFW) rules configured and tested
- [ ] Database user privileges restricted (not root)
- [ ] `.env` file permissions set to 600
- [ ] Storage directories have proper permissions (775/750)
- [ ] Nginx security headers configured
- [ ] Rate limiting tested on sensitive endpoints
- [ ] File upload security validation verified
- [ ] Input sanitization middleware active
- [ ] Content Security Policy (CSP) configured
- [ ] HSTS header enabled for HTTPS
- [ ] X-Frame-Options, X-Content-Type-Options headers set
- [ ] Referrer-Policy configured
- [ ] Security event logging monitored

## 💳 Midtrans Production Deployment Checklist

**Sebelum Go-Live:**

- [ ] **Akun Midtrans sudah diverifikasi:**
  - [ ] Business information sudah lengkap
  - [ ] Dokumen legal sudah di-upload
  - [ ] Bank account untuk settlement sudah dikonfigurasi
  - [ ] Status akun: **Verified** (bukan Pending)

- [ ] **Production Keys sudah didapat:**
  - [ ] Production Server Key (dimulai dengan `Mid-server-`)
  - [ ] Production Client Key (dimulai dengan `Mid-client-`)
  - [ ] Merchant ID (jika ada)
  - [ ] **PENTING:** Jangan gunakan Sandbox keys di production!

- [ ] **Konfigurasi `.env` di Production Server:**
  - [ ] `MIDTRANS_SERVER_KEY` = Production Server Key
  - [ ] `MIDTRANS_CLIENT_KEY` = Production Client Key
  - [ ] `MIDTRANS_IS_PRODUCTION=true` (CRITICAL!)
  - [ ] `APP_URL=https://your-domain.com` (HTTPS, bukan HTTP)
  - [ ] `APP_ENV=production`
  - [ ] `APP_DEBUG=false`

- [ ] **URL Endpoints dikonfigurasi di Midtrans Dashboard:**
  - [ ] Login ke [Midtrans Dashboard](https://dashboard.midtrans.com) → Production
  - [ ] Settings → Configuration → URL Settings
  - [ ] Payment Notification URL: `https://your-domain.com/payment/callback`
  - [ ] Finish Redirect URL: `https://your-domain.com/payment/finish`
  - [ ] Unfinish Redirect URL: `https://your-domain.com/payment/unfinish`
  - [ ] Error Redirect URL: `https://your-domain.com/payment/error`
  - [ ] Semua URL menggunakan **HTTPS** (bukan HTTP)
  - [ ] Semua URL sudah di-test dan accessible

- [ ] **SSL/HTTPS sudah aktif:**
  - [ ] SSL certificate sudah terinstall (Let's Encrypt atau lainnya)
  - [ ] Domain bisa diakses via HTTPS tanpa error
  - [ ] Nginx sudah dikonfigurasi untuk redirect HTTP → HTTPS

- [ ] **Laravel Cache sudah di-clear dan di-optimize:**
  ```bash
  php artisan config:clear
  php artisan cache:clear
  php artisan optimize
  ```

- [ ] **Testing Production Setup:**
  - [ ] Test top-up dengan amount kecil (misalnya Rp 10.000)
  - [ ] Gunakan kartu **real** (bukan test card) untuk test pertama
  - [ ] Verifikasi payment popup muncul dengan benar
  - [ ] Verifikasi redirect setelah payment success
  - [ ] Verifikasi saldo wallet ter-update
  - [ ] Verifikasi webhook diterima (cek di Midtrans Dashboard → Transactions)
  - [ ] Verifikasi transaction status ter-update di database
  - [ ] Jalankan sanity test penting: `php artisan test --filter=subscription_renewal_command`

- [ ] **Monitoring Setup:**
  - [ ] Laravel logs monitoring: `tail -f storage/logs/laravel.log`
  - [ ] Midtrans Dashboard monitoring: Check transactions regularly
  - [ ] Email notifications untuk error transactions
  - [ ] Database backup sebelum go-live

- [ ] **Payment Methods yang Tersedia:**
  - [ ] Semua payment method sudah aktif di Midtrans Dashboard
  - [ ] Credit/Debit Card sudah aktif
  - [ ] Bank Transfer (BCA, Mandiri, BNI, BRI) sudah aktif
  - [ ] Virtual Account sudah aktif
  - [ ] E-Wallet (GoPay, OVO, DANA) sudah aktif (jika diperlukan)

**Setelah Go-Live:**

- [ ] Monitor transaksi pertama dengan teliti
- [ ] Cek webhook notifications di Midtrans Dashboard
- [ ] Verifikasi settlement berjalan dengan benar
- [ ] Monitor Laravel logs untuk error
- [ ] Test semua payment methods yang tersedia
- [ ] Verifikasi email notifications untuk transaksi

## 🔄 Backup Strategy

### Database Backup

**Manual Backup:**
```bash
# Create backup directory
sudo mkdir -p /backups/noteds
sudo chown www-data:www-data /backups/noteds

# Manual backup
mysqldump -u noteds_user -p noteds_production > /backups/noteds/backup_$(date +%Y%m%d_%H%M%S).sql

# Compressed backup
mysqldump -u noteds_user -p noteds_production | gzip > /backups/noteds/backup_$(date +%Y%m%d_%H%M%S).sql.gz
```

**Automated Backup Script:**
Create `/usr/local/bin/noteds-backup.sh`:
```bash
#!/bin/bash

BACKUP_DIR="/backups/noteds"
DB_USER="noteds_user"
DB_NAME="noteds_production"
RETENTION_DAYS=30

# Create backup directory if not exists
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u $DB_USER -p$(grep DB_PASSWORD /var/www/noteds/.env | cut -d '=' -f2) $DB_NAME | gzip > $BACKUP_DIR/db_$(date +%Y%m%d_%H%M%S).sql.gz

# File storage backup (exclude cache and logs)
tar -czf $BACKUP_DIR/storage_$(date +%Y%m%d_%H%M%S).tar.gz \
    -C /var/www/noteds \
    --exclude='storage/framework/cache' \
    --exclude='storage/framework/sessions' \
    --exclude='storage/framework/views' \
    --exclude='storage/logs' \
    storage/app

# Remove old backups (older than RETENTION_DAYS)
find $BACKUP_DIR -type f -name "*.sql.gz" -mtime +$RETENTION_DAYS -delete
find $BACKUP_DIR -type f -name "*.tar.gz" -mtime +$RETENTION_DAYS -delete

echo "Backup completed: $(date)"
```

Make executable:
```bash
sudo chmod +x /usr/local/bin/noteds-backup.sh
```

**Automated Backup Cronjob:**
```bash
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/noteds-backup.sh >> /var/log/noteds-backup.log 2>&1
```

### File Storage Backup

**Local Backup:**
```bash
# Sync to backup location
sudo rsync -av --delete /var/www/noteds/storage/app /backups/noteds/storage/
```

**Cloud Backup (S3):**
```bash
# Install AWS CLI
sudo apt install awscli -y

# Configure AWS credentials
aws configure

# Sync to S3
aws s3 sync /var/www/noteds/storage/app s3://your-bucket/noteds/storage --delete

# Automated S3 backup script
cat > /usr/local/bin/noteds-s3-backup.sh << 'EOF'
#!/bin/bash
aws s3 sync /var/www/noteds/storage/app s3://your-bucket/noteds/storage --delete
echo "S3 backup completed: $(date)"
EOF

sudo chmod +x /usr/local/bin/noteds-s3-backup.sh

# Add to crontab (daily at 3 AM)
# 0 3 * * * /usr/local/bin/noteds-s3-backup.sh >> /var/log/noteds-s3-backup.log 2>&1
```

**Backup Verification:**
```bash
# Test database backup restore (on test database)
mysql -u noteds_user -p noteds_test < /backups/noteds/backup_YYYYMMDD.sql

# Verify backup files
ls -lh /backups/noteds/
du -sh /backups/noteds/
```

### Backup Restoration

**Database Restoration:**
```bash
# Stop application (optional, for zero downtime use replication)
sudo systemctl stop php8.2-fpm

# Restore database
gunzip < /backups/noteds/db_YYYYMMDD_HHMMSS.sql.gz | mysql -u noteds_user -p noteds_production

# Or from uncompressed backup
mysql -u noteds_user -p noteds_production < /backups/noteds/backup_YYYYMMDD.sql

# Start application
sudo systemctl start php8.2-fpm
```

**File Storage Restoration:**
```bash
# From local backup
sudo rsync -av /backups/noteds/storage/ /var/www/noteds/storage/app/

# From S3
aws s3 sync s3://your-bucket/noteds/storage /var/www/noteds/storage/app

# Fix permissions
sudo chown -R www-data:www-data /var/www/noteds/storage
sudo chmod -R 775 /var/www/noteds/storage
```

## 📊 Performance Optimization

### PHP-FPM Tuning
Edit `/etc/php/8.2/fpm/pool.d/www.conf`:
```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

### OpCache
Edit `/etc/php/8.2/fpm/php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### Redis Caching (Recommended)
```bash
sudo apt install redis-server -y
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

Update `.env`:
```env
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CLIENT=phpredis
REDIS_CACHE_DB=1
```

**Note:** Aplikasi akan otomatis menggunakan Redis jika `REDIS_HOST` tersedia, fallback ke database jika tidak.

### Database Indexes
Indexes sudah ditambahkan untuk performa optimal:
- Notes: `is_public`, `status`, `ecosystem_category`, `language`, `price`, `created_at`
- Transactions: `status`, `seller_id`, `created_at`, `note_id`
- Users: `role`, `username`

### CDN Configuration (Optional)
Untuk static assets (images, CSS, JS), konfigurasi CDN di `.env`:
```env
CDN_URL=https://cdn.yourdomain.com
```

Jika tidak di-set, akan menggunakan `APP_URL`.

### Image Processing (Optional)
Install intervention/image untuk automatic image resizing:
```bash
composer require intervention/image
```

Service `ImageProcessingService` sudah tersedia untuk generate multiple sizes (thumbnail, medium, large).

### Laravel Telescope Query Monitoring
Telescope sudah dikonfigurasi untuk query monitoring:
- Query watcher enabled by default
- Slow query threshold: 100ms (configurable via `TELESCOPE_SLOW_QUERY_THRESHOLD`)
- Access di `/telescope` → Queries tab

Environment variables:
```env
TELESCOPE_ENABLED=true
TELESCOPE_QUERY_WATCHER=true
TELESCOPE_SLOW_QUERY_THRESHOLD=100
```

## 🚨 Troubleshooting

### 413 Request Entity Too Large (Nginx)
Jika mengalami error "413 Request Entity Too Large" saat upload file:

#### Untuk aaPanel Users (Recommended):

1. **Update PHP Configuration via aaPanel:**
   - Buka aaPanel → **App Store** → Cari **PHP-8.3** (atau versi PHP yang digunakan)
   - Klik **Setting** → Pilih **Limit of upload**
   - Set **upload_max_filesize** ke **1000M** (atau sesuai kebutuhan)
   - Klik **Save**
   - Pilih **Configuration** tab
   - Pastikan:
     - `post_max_size`: **2000M**
     - `memory_limit`: **512M**
     - `max_execution_time`: **1000** (atau lebih besar)
   - Klik **Save**

2. **Update Nginx Configuration via aaPanel:**
   
   **Cara 1: Via Files Manager (Recommended)**
   - Buka aaPanel → **Files**
   - Navigate ke `/www/server/panel/vhost/nginx/`
   - Cari file konfigurasi domain Anda (misal: `noteds.com.conf`)
   - Edit file tersebut
   - Tambahkan atau update di dalam block `server { ... }`:
   ```nginx
   client_max_body_size 200M;  # Increase dari default 1M atau 50M (minimal 200M untuk file besar)
   client_body_buffer_size 128k;
   client_body_timeout 900s;   # 15 menit untuk file sangat besar (51MB+)
   client_header_timeout 900s;
   send_timeout 900s;
   ```
   - **Save** file
   
   **Cara 2: Via Nginx Settings**
   - Buka aaPanel → **App Store** → **Nginx** → **Setting**
   - Pilih **Configuration file**
   - Edit file konfigurasi untuk domain Anda
   - Tambahkan konfigurasi di atas
   - **Save** dan **Reload** Nginx
   
   **Reload Nginx:**
   - Buka aaPanel → **App Store** → **Nginx** → **Setting** → **Service** → **Reload**

3. **Update PHP-FPM Timeout (PENTING untuk file besar 51MB+):**
   - Buka aaPanel → **App Store** → **PHP-8.3** → **Setting**
   - Pilih **Limit of timeout** tab
   - Set ke **1000** (atau lebih besar, seperti 1500 untuk file sangat besar)
   - Klik **Save**
   - Pilih **FPM profile** tab
   - Cari `request_terminate_timeout` dan set ke **900** atau lebih besar (dalam detik)
   - Jika tidak ada, tambahkan: `request_terminate_timeout = 900`
   - Klik **Save**
   
   **Catatan:** File 51MB membutuhkan waktu lebih lama untuk upload, terutama dengan koneksi lambat. Timeout harus cukup besar untuk menghindari error di akhir upload.
   
4. **Restart Services via aaPanel:**
   - Buka aaPanel → **App Store** → **Nginx** → **Setting** → **Service** → **Reload**
   - Buka aaPanel → **App Store** → **PHP-8.3** → **Setting** → **Service** → **Restart**

5. **Clear Laravel Cache:**
   ```bash
   cd /www/wwwroot/noteds.com  # atau path aplikasi Anda
   php artisan config:clear
   php artisan cache:clear
   ```

6. **Verifikasi Konfigurasi:**
   ```bash
   # Check PHP settings
   php -i | grep -E "upload_max_filesize|post_max_size|max_execution_time|memory_limit"
   
   # Check Nginx config
   nginx -t
   
   # Check PHP-FPM timeout
   grep request_terminate_timeout /etc/php/8.3/fpm/pool.d/www.conf
   ```

#### Untuk Manual Configuration (Jika tidak menggunakan aaPanel):

1. **Update Nginx Configuration:**
   Edit `/etc/nginx/sites-available/noteds` dan pastikan:
   ```nginx
   client_max_body_size 200M;  # Increase dari default 1M atau 50M (minimal 200M untuk file besar)
   client_body_buffer_size 128k;
   client_body_timeout 900s;   # 15 menit untuk file sangat besar (51MB+)
   client_header_timeout 900s;
   send_timeout 900s;
   ```

2. **Update PHP Configuration:**
   Edit `/etc/php/8.3/fpm/php.ini`:
   ```ini
   upload_max_filesize = 1000M
   post_max_size = 2000M
   max_execution_time = 1000
   max_input_time = 900
   memory_limit = 512M
   ```

3. **Update PHP-FPM Configuration:**
   Edit `/etc/php/8.3/fpm/pool.d/www.conf`:
   ```ini
   request_terminate_timeout = 900  # 15 menit untuk file besar
   ```

4. **Restart Services:**
   ```bash
   sudo systemctl reload nginx
   sudo systemctl restart php8.3-fpm
   ```

#### Jika Menggunakan Cloudflare:

Jika Anda menggunakan Cloudflare sebagai CDN/proxy, pastikan:

1. **Cloudflare Timeout Settings:**
   - Buka Cloudflare Dashboard → **Speed** → **Optimization**
   - Pastikan **HTTP/2** dan **HTTP/3** enabled
   - Buka **Network** → **Protocol Tunneling**
   - Pastikan timeout cukup besar untuk file upload

2. **Cloudflare Page Rules (Optional):**
   - Buat page rule untuk route `/notes/upload-background` dengan:
     - **Cache Level**: Bypass
     - **Browser Cache TTL**: Respect Existing Headers
     - **Security Level**: Medium

3. **Cloudflare Worker (Advanced - Optional):**
   - Jika menggunakan Cloudflare Workers, pastikan timeout worker cukup besar
   - Default worker timeout adalah 30 detik (Free plan) atau 30 detik (Paid plan)
   - Untuk file upload besar, pertimbangkan untuk bypass Cloudflare Worker

**Catatan:** Error 524 (Cloudflare Timeout) terjadi ketika Cloudflare menunggu response dari origin server lebih dari 100 detik. Pastikan:
- Origin server (Nginx/PHP) timeout cukup besar (minimal 900 detik)
- Koneksi antara Cloudflare dan origin server stabil
- File tidak terlalu besar (pertimbangkan chunked upload untuk file >100MB)

4. **Verify Configuration:**
   ```bash
   sudo nginx -t  # Test nginx config
   php -i | grep upload_max_filesize  # Check PHP settings
   ```

### 500 Internal Server Error
- Check `storage/logs/laravel.log`
- Verify file permissions
- Run `php artisan config:clear`
- Check PHP-FPM error logs: `sudo tail -f /var/log/php8.2-fpm.log`

### Queue Not Processing
```bash
sudo supervisorctl status
sudo supervisorctl restart noteds-worker:*
```

### Database Connection Issues
- Verify `.env` credentials
- Check MySQL service: `sudo systemctl status mysql`
- Test connection: `mysql -u noteds_user -p noteds_production`

### Assets Not Loading / JavaScript Errors (Swal is not defined)
- **CRITICAL:** Run `npm run build` in production (Vite assets must be compiled)
  ```bash
  cd /var/www/noteds
  sudo -u www-data npm install
  sudo -u www-data npm run build
  ```
- Verify build output exists: `ls -la public/build/`
- Check Vite manifest: `public/build/.vite/manifest.json` should exist
- Clear Laravel cache after build:
  ```bash
  sudo -u www-data php artisan optimize:clear
  sudo -u www-data php artisan optimize
  ```
- Check Nginx config for proper root and asset serving
- Verify file permissions (storage and public/build should be writable)
- Clear browser cache (hard refresh: Ctrl+Shift+R or Cmd+Shift+R) 
- Check browser console for 404 errors on JavaScript files
- Verify `.env` has correct `APP_URL` (must match your domain)
- **If Swal is still undefined after build:**
  - Check if `resources/js/app.js` is being compiled correctly
  - Verify `sweetalert2` is in `package.json` dependencies
  - Run `npm install` again to ensure dependencies are installed
  - Check browser Network tab to see if `app.js` is loading
  - Verify Vite is serving assets correctly (check Nginx config)

### Ollama Connection Issues
- Verify Ollama is running: `sudo systemctl status ollama`
- Test connection: `curl http://localhost:11434/api/tags`
- Check `.env` `OLLAMA_URL` is correct
- Check firewall rules if using remote Ollama server
- View Ollama logs: `sudo journalctl -u ollama -f`

### AI Service Issues

#### Ollama Status & Error Handling

**Status Ollama dari Log:**
- ✅ Ollama service berjalan dengan baik
- ✅ Model berhasil dimuat (`model r>` menunjukkan model sedang dimuat/ready)
- ✅ Server aktif dan merespons request
- ✅ CPU backend loaded dengan baik

**Error 500 di Ollama:**
- Error 500 adalah **normal dan transient** (sementara)
- Dapat terjadi karena:
  - Model sedang loading/memuat
  - Request timeout (jika prompt terlalu panjang atau CPU lambat)
  - Memory sementara tidak cukup (jarang)
  - Model error sementara (jarang)

**Perbaikan yang Sudah Diterapkan:**
- ✅ **Auto-retry mechanism:** Error 500/503 akan di-retry otomatis sampai 2 kali dengan delay 2 detik
- ✅ **Model availability check:** Sistem memeriksa apakah model tersedia sebelum membuat request
- ✅ **Enhanced error logging:** Log detail untuk debugging (status code, error message, attempt count)
- ✅ **Connection exception handling:** Menangani network errors dengan baik
- ✅ **Response validation:** Memvalidasi struktur response sebelum digunakan

**Cara Memeriksa Status Ollama:**
```bash
# Cek status service
systemctl status ollama

# Cek log Ollama
journalctl -u ollama -f

# Cek model yang tersedia
curl http://localhost:11434/api/tags

# Test API (format JSON yang benar)
curl -X POST http://localhost:11434/api/generate \
  -H "Content-Type: application/json" \
  -d '{
    "model": "llama3.2",
    "prompt": "Hello, how are you?",
    "stream": false
  }'

# Atau gunakan script test otomatis (jika tersedia)
bash test-ollama.sh
```

**Script Test Otomatis:**
File `test-ollama.sh` sudah disediakan di root project untuk test komprehensif:
- ✅ Check service status
- ✅ Check available models
- ✅ Verify model availability (llama3.2)
- ✅ Test API generate endpoint
- ✅ Check configuration
- ✅ Check process and memory usage

**Troubleshooting Ollama Error 500:**
1. **Model sedang loading:** Tunggu beberapa detik, retry mechanism akan menangani ini
2. **Memory tidak cukup:** Cek memory usage dengan `free -h`, pertimbangkan mengurangi `num_ctx` di config
3. **Request timeout:** Increase timeout di `.env` (`OLLAMA_TIMEOUT=180`)
4. **Model tidak tersedia:** Pastikan model sudah di-download dengan `ollama pull llama3.2`
5. **CPU overload:** Monitor CPU usage, pertimbangkan mengurangi `OLLAMA_NUM_THREADS`

**Konfigurasi Optimal untuk CPU-only VPS:**
Konfigurasi ini sudah termasuk di bagian **Environment Configuration** (step 4) di atas. Pastikan semua variabel berikut ada di file `.env`:

```env
OLLAMA_URL=http://localhost:11434
OLLAMA_MODEL=llama3.2
OLLAMA_NUM_THREADS=8      # Sesuaikan dengan jumlah CPU cores (auto-detect jika null)
OLLAMA_NUM_CTX=4096       # Context window (4096 = 4K tokens, 8192 = 8K tokens)
OLLAMA_BATCH_SIZE=512     # Batch size untuk CPU inference
OLLAMA_TIMEOUT=120        # Timeout dalam detik (120 = 2 menit untuk CPU)
OLLAMA_USE_MLOCK=false    # Lock memory (perlu root, untuk performa lebih baik)
OLLAMA_NUMA=false         # NUMA optimization (untuk multi-socket CPU)
```

**Catatan Penting:**
- `OLLAMA_NUM_THREADS`: Jika di-set `null` atau tidak di-set, sistem akan auto-detect jumlah CPU cores
- `OLLAMA_NUM_CTX`: Jika memory VPS terbatas (< 4GB), kurangi ke `2048` atau `1024`
- `OLLAMA_TIMEOUT`: Increase ke `180` atau `240` jika request sering timeout
- `OLLAMA_USE_MLOCK`: Set ke `true` hanya jika memiliki akses root dan ingin performa lebih baik
- `OLLAMA_NUMA`: Set ke `true` hanya untuk server dengan multi-socket CPU

**Setelah update `.env`, jalankan:**
```bash
php artisan config:clear
php artisan cache:clear
```

#### AI Service Issues

#### AI Request Fails
- Check Ollama service: `sudo systemctl status ollama`
- Check AI service availability: `curl http://localhost:11434/api/tags`
- View AI error logs: `tail -f storage/logs/laravel.log | grep -i "ai\|ollama"`
- Verify queue worker is running: `sudo supervisorctl status noteds-worker:*`
- Check API keys for external services (Stability AI, Unsplash, RunwayML) in `.env`

#### AI Request Timeout
- Increase timeout in `.env`: `OLLAMA_TIMEOUT=120` (default: 120 seconds)
- Check server resources (CPU, RAM) during AI inference
- Consider using smaller models for CPU-only inference
- Monitor queue jobs: `php artisan queue:work --queue=default`

#### External AI API Errors
- **Stability AI:** Check API key, verify credits, check rate limits
- **Unsplash:** Check API key, verify rate limits, check connection
- **RunwayML:** Check API key, verify credits, check API endpoint
- All errors are logged with detailed information in `storage/logs/laravel.log`
- Check error logs for specific HTTP status codes (401, 403, 429, 500+)

### Midtrans Payment Issues / Top-up Tidak Bisa

#### 1. Verifikasi Konfigurasi `.env`
```bash
# Pastikan semua key sudah diisi dengan benar
MIDTRANS_SERVER_KEY=your_server_key_here
MIDTRANS_CLIENT_KEY=your_client_key_here
MIDTRANS_IS_PRODUCTION=false  # false untuk sandbox, true untuk production
MIDTRANS_MERCHANT_ID=your_merchant_id
```

**PENTING:** Setelah update `.env`, jalankan:
```bash
php artisan config:clear
php artisan cache:clear
```

#### 2. Cek Log Laravel untuk Error Detail
```bash
tail -f storage/logs/laravel.log
```

Cari log dengan keyword:
- "Midtrans Configuration Check"
- "Midtrans Snap Token Error"
- "Snap Token generated successfully"

#### 3. Verifikasi Midtrans Keys
- **Sandbox:** Pastikan menggunakan Sandbox keys dari [Midtrans Dashboard](https://dashboard.sandbox.midtrans.com)
- **Production:** Pastikan menggunakan Production keys dari [Midtrans Dashboard](https://dashboard.midtrans.com)
- **Server Key** harus dimulai dengan `SB-Mid-server-` untuk sandbox atau `Mid-server-` untuk production
- **Client Key** harus dimulai dengan `SB-Mid-client-` untuk sandbox atau `Mid-client-` untuk production

#### 4. Test Snap Token Generation
Buat route test sementara di `routes/web.php`:
```php
Route::get('/test-midtrans', function() {
    \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
    \Midtrans\Config::$isProduction = config('services.midtrans.is_production', false);
    
    $params = [
        'transaction_details' => [
            'order_id' => 'test-' . time(),
            'gross_amount' => 10000,
        ],
    ];
    
    try {
        $token = \Midtrans\Snap::getSnapToken($params);
        return response()->json(['success' => true, 'token' => $token]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
});
```

Akses `/test-midtrans` di browser untuk test.

#### 5. Verify all URL endpoints di Midtrans Dashboard
Login ke [Midtrans Dashboard](https://dashboard.midtrans.com) → Settings → Configuration → URL Settings:
- **Payment Notification URL:** `https://your-domain.com/payment/callback`
- **Finish Redirect URL:** `https://your-domain.com/payment/finish`
- **Unfinish Redirect URL:** `https://your-domain.com/payment/unfinish`
- **Error Redirect URL:** `https://your-domain.com/payment/error`

#### 6. Test dengan Kartu Test Midtrans
- **Visa:** 4811 1111 1111 1114
- **Mastercard:** 5211 1111 1111 1117
- **CVV:** 123
- **Expiry:** Any future date (e.g., 12/25)

#### 7. Common Issues & Solutions

**Issue: "Payment gateway belum dikonfigurasi"**
- Cek apakah `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY` sudah diisi di `.env`
- Jalankan `php artisan config:clear`

**Issue: "Snap Token is empty"**
- Cek Server Key apakah benar
- Cek apakah `MIDTRANS_IS_PRODUCTION` sesuai dengan keys yang digunakan
- Cek Laravel log untuk error detail dari Midtrans API

**Issue: "Snap.js tidak ter-load"**
- Cek browser console untuk error
- Pastikan Client Key sudah benar
- Pastikan URL Snap.js sesuai dengan environment (sandbox vs production)

#### Console Errors di Development (Aman untuk Production)

**Error yang mungkin muncul di console saat development:**

1. **`postMessage` origin mismatch error:**
   ```
   Failed to execute 'postMessage' on 'DOMWindow': The target origin provided 
   ('https://app.sandbox.midtrans.com') does not match the recipient window's origin 
   ('http://noteds.test').
   ```
   - **Status:** ✅ **AMAN untuk Production**
   - **Penyebab:** Cross-origin communication antara Midtrans Snap.js dan local development domain
   - **Di Production:** Error ini **tidak akan muncul** karena:
     - Production menggunakan `https://app.midtrans.com` (bukan sandbox)
     - Domain production menggunakan HTTPS (bukan HTTP)
     - Midtrans Snap.js sudah handle cross-origin dengan benar untuk production domains
   - **Tindakan:** Tidak perlu action, ini hanya warning di development

2. **404 Error pada 3DS method-response endpoint:**
   ```
   POST https://api.sandbox.midtrans.com/v2/3ds/method-response/... 404 (Not Found)
   ```
   - **Status:** ✅ **AMAN untuk Production**
   - **Penyebab:** Beberapa payment method di sandbox tidak memiliki endpoint 3DS yang lengkap
   - **Di Production:** Error ini **tidak akan muncul** karena:
     - Production menggunakan payment method yang sudah diverifikasi dan lengkap
     - Semua endpoint 3DS tersedia di production environment
     - Sandbox memiliki beberapa limitation yang tidak ada di production
   - **Tindakan:** Tidak perlu action, ini normal di sandbox dan tidak mempengaruhi transaksi

**Kesimpulan:**
- ✅ Semua error console yang muncul di development adalah **normal** dan **tidak akan muncul di production**
- ✅ Payment flow tetap berfungsi dengan baik meskipun ada error di console
- ✅ Error ini tidak mempengaruhi keamanan atau fungsionalitas di production
- ✅ Pastikan `MIDTRANS_IS_PRODUCTION=true` di production untuk menggunakan production endpoints
- Cek apakah ada firewall yang block CDN Midtrans

**Issue: "Webhook tidak diterima"**
- Pastikan URL webhook sudah dikonfigurasi di Midtrans Dashboard
- Pastikan route `/payment/callback` bisa diakses (test dengan `curl`)
- Cek Laravel log untuk webhook yang diterima
- Pastikan HTTPS aktif (Midtrans memerlukan HTTPS di production)

**Issue: "HTTP ERROR 404" saat redirect ke payment method (BCA KlikPay, dll)**
- **Ini adalah masalah dari Midtrans Sandbox, bukan dari kode aplikasi**
- Beberapa payment method seperti BCA KlikPay tidak tersedia di sandbox environment
- **Solusi:** Gunakan payment method yang tersedia di sandbox:
  - ✅ **Credit/Debit Card** (Visa/Mastercard) - **Direkomendasikan untuk testing**
  - ✅ **Bank Transfer** (Mandiri, BCA, BNI, BRI)
  - ✅ **Virtual Account** (BCA, Mandiri, BNI, BRI)
  - ✅ **E-Wallet** (GoPay, OVO, DANA)
  - ❌ **BCA KlikPay** - Tidak tersedia di sandbox
  - ❌ Beberapa payment method khusus lainnya mungkin tidak tersedia di sandbox
- **Untuk production:** Semua payment method akan tersedia setelah akun Midtrans diverifikasi
- Jika error 404 muncul, tutup popup dan pilih payment method lain yang tersedia

#### 8. Debug Commands
```bash
# Clear all cache
php artisan optimize:clear

# Check config
php artisan config:show services.midtrans

# Test webhook endpoint
curl -X POST https://your-domain.com/payment/callback \
  -H "Content-Type: application/json" \
  -d '{"order_id":"test-123","transaction_status":"settlement"}'

# View real-time logs
tail -f storage/logs/laravel.log | grep -i midtrans
```
- **Webhook Improvements (2025-11-03):**
  - Webhook route sudah exempt dari CSRF protection
  - Duplicate processing protection sudah ditambahkan
  - Better error handling dan logging
  - Amount verification untuk security
  - Support untuk semua transaction status (settlement, capture, pending, challenge, deny, expire, cancel)
- **Payment Redirect URLs (2025-11-05):**
  - Added finish, unfinish, and error redirect handlers
  - Proper transaction status updates on redirect
  - User-friendly error messages
  - All redirect URLs configured in Snap.js callbacks

### Featured Notes Issues
- **Featured notes not displaying:**
  - Check if featured notes are active: `status = 'active'` and `start_date <= now()` and `end_date >= now()`
  - Verify featured notes have correct location set
  - Check if auto-expire command is running: `php artisan featured:expire`
  - View featured notes in admin panel: `/admin/featured-notes`
- **Analytics not tracking:**
  - Verify impressions are incremented on display (landing page, marketplace)
  - Verify clicks are incremented on purchase
  - Check database: `featured_notes` table columns `impressions` and `clicks`
  - Analytics tracking requires authenticated users
- **Auto-approve not working:**
  - Verify user has active premium subscription: `subscriptions` table
  - Check `hasPremium()` method in User model
  - Auto-approve only works for premium users
  - Non-premium users require admin approval
- **Popup modals not appearing:**
  - Check if featured notes exist for popup locations (`popup_welcome`, `popup_exit`, `popup_interstitial`)
  - Verify localStorage keys: `popup_welcome_shown`, `popup_exit_shown_today`, `popup_interstitial_shown_today`
  - Check browser console for JavaScript errors
  - Popups only show once per day (for exit intent and interstitial)
  - Welcome popup only shows for non-authenticated users

## 🔥 Firewall Configuration (UFW)

```bash
# Enable UFW
sudo ufw enable

# Allow SSH (important - do this first!)
sudo ufw allow 22/tcp

# Allow HTTP and HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Allow Ollama if on same server (optional)
sudo ufw allow 11434/tcp

# Check status
sudo ufw status
```

## 📞 Support

For deployment issues, refer to:
- [Laravel 12 Deployment Docs](https://laravel.com/docs/12.x/deployment)
- [DigitalOcean Laravel Setup](https://www.digitalocean.com/community/tutorials/how-to-install-and-configure-laravel-with-nginx-on-ubuntu-22-04)
- [Ollama Installation](https://ollama.com/download)
- [Nginx Performance Tuning](https://www.nginx.com/blog/tuning-nginx/)

## 📝 Additional Notes

### Laravel Version
This project uses **Laravel 12** (not Laravel 11). Ensure all commands are compatible.

### UUID Primary Keys
All database tables use UUID primary keys. Ensure MySQL supports UUID functions.

### Premium Features
AI Memory Platform features require:
- Active premium subscription
- Ollama service running and accessible
- Sufficient server resources for AI processing

### Featured Notes System
Featured Notes advertising system features:
- **Payment:** Featured notes are paid via wallet (deducted immediately on request)
- **Auto-approve:** Premium users get instant approval (no admin review needed)
- **Admin approval:** Non-premium users require admin approval before activation
- **Refund:** If admin rejects, full refund is automatically processed
- **Auto-expire:** Expired featured notes are automatically updated daily via scheduled command
- **Analytics:** Impressions and clicks are tracked automatically
- **Pricing:** Configurable per location and duration in admin settings
- **Locations:** Landing Hero, Carousel, Marketplace Banner/Grid, Popup modals
- **Scheduled command:** `featured:expire` runs daily at 01:00 WIB

### Note History & Versioning System
- **Migration:** `note_histories` table untuk tracking semua perubahan note
- **Features:**
  - History tracking untuk created, updated, sold actions
  - Buyer history untuk seller (list semua buyer yang pernah membeli)
  - Update history dengan versioning (detail perubahan setiap update)
  - Prevent delete jika note sudah dijual (untuk melindungi data buyer)
- **Access:** Seller/original creator bisa lihat buyer history dan update history di notes.show

### AI Chat untuk Seller Profile (Public Feature)
- **Routes:** `/u/{username}/ai-chat` (GET), `/u/{username}/ai-chat/ask` (POST)
- **Features:**
  - AI chat interface untuk bertanya tentang notes seller
  - Context dari semua notes public seller
  - Real-time chat dengan referenced notes links
  - **Semua user bisa akses** (tidak perlu premium)
- **Access Points:**
  - Tombol "AI" di marketplace card note
  - Tombol "Ask AI" di detail note
  - Tombol "Ask AI About [Seller Name]'s Notes" di profile seller

### Collections Enhancement
- **Features:**
  - Tombol "Add Purchased Notes" di collection header
  - Dropdown untuk memilih purchased notes yang belum ada di collection
  - Validasi: Hanya purchased notes yang bisa ditambahkan
  - Auto-filter: Notes yang sudah ada tidak muncul di dropdown

### Sale Mode System
- **Scarcity Mode:**
  - One-time purchase per user
  - Buyer bisa resell dengan harga custom
  - Original creator dapat komisi di setiap penjualan
  - Grace period untuk repurchase (configurable, default: 30 hari)
  - Relist price multiplier untuk repurchase setelah grace period (default: 1.5x)
  - Ownership transfer ke buyer
- **Standard Mode:**
  - Multiple sales allowed
  - Buyer tidak bisa resell
  - Tidak ada komisi untuk original creator
  - Ownership tetap dengan seller
- **Admin Features:**
  - Filter notes by sale mode
  - Analytics dashboard dengan revenue comparison
  - Detailed repurchase report dengan metrics

### Resell Flow & One-Time Sale System
- **Features:**
  - One-time sale: Buyer yang sudah menjual note tidak bisa akses lagi
  - Original creator commission: Creator selalu dapat komisi di setiap resell
  - Ownership transfer: Note ownership dipindahkan ke buyer baru
  - Access control: Hanya current owner yang bisa akses full content
  - Purchase validation: Buyer tidak bisa membeli lagi note yang sudah pernah dibeli
  - Warning messages: Peringatan jelas sebelum dan setelah menjual

### Profile Features
- **Avatar Upload:**
  - Upload file (JPG, PNG, GIF - Max 2MB)
  - Atau gunakan URL
  - Storage: `storage/app/public/avatars/{user_id}/`
- **Share Functionality:**
  - Share buttons: Facebook, Twitter, WhatsApp, LinkedIn, Copy Link
  - Open Graph meta tags untuk social media preview
  - Twitter Card meta tags
  - Available di: Profile seller, Marketplace index, Marketplace detail note

### Storage Considerations
- Note attachments are stored in `storage/app/private/attachments`
- Ensure sufficient disk space for user uploads
- Consider using cloud storage (S3) for production scale
- S3 backup configuration available via Admin Settings UI

### Email Configuration
- Contact form emails are sent to support email (configurable via Admin Settings)
- Default support email: Uses `MAIL_FROM_ADDRESS` from `.env`
- Email sending uses Laravel Mail system (SMTP, Sendmail, SES, etc.)
- Contact form uses `ContactMail` mailable class
- Email failures are logged but don't fail the contact form submission

### Midtrans Integration Details
- Aplikasi menggunakan **Midtrans PHP SDK** (package: `midtrans/midtrans-php` - sudah di `composer.json`)
- Payment flow menggunakan **Snap.js** untuk frontend payment UI
- Backend menggunakan **Server Key** untuk generate Snap Token via `WalletController`
- Frontend menggunakan **Client Key** untuk load Snap.js library
- Webhook handler otomatis update status transaksi setelah payment selesai
- Route webhook: `/wallet/webhook` (handler: `WalletController::webhook()`)
- Semua transaksi (topup wallet, premium subscription, purchase notes) menggunakan Midtrans
- **PENTING:** Webhook harus dapat diakses dari internet dengan HTTPS di production
- Test thoroughly dengan sandbox environment sebelum switch ke production
- Monitor transaction logs di Midtrans Dashboard untuk debugging
- **Security:** Jangan commit API keys ke repository, selalu gunakan `.env`

**Recent Improvements (2025-11-03):**
- ✅ Dynamic Midtrans URL (sandbox/production) based on environment config
- ✅ Webhook CSRF exemption untuk Midtrans webhook calls
- ✅ Duplicate processing prevention (double-check sebelum process)
- ✅ Amount verification untuk security
- ✅ Comprehensive status handling (settlement, capture, pending, challenge, deny, expire, cancel)
- ✅ Better error logging dan response handling
- ✅ Max amount validation (100M) untuk topup
- ✅ Midtrans configuration check sebelum process

**AI Features Improvements (2025-01-11):**
- ✅ Enhanced error handling untuk semua AI APIs (Ollama, Stability AI, Unsplash, RunwayML)
- ✅ Request queuing untuk high traffic (`ProcessAiRequest` job) - dapat diproses secara asinkron
- ✅ Performance tracking & monitoring untuk AI requests (duration logging, error tracking)
- ✅ Null checks dan validation improvements di semua AI controllers
- ✅ Retry mechanism untuk external APIs (Stability AI: 2x dengan delay 2s, Unsplash: 2x dengan delay 100ms, RunwayML: 2x dengan delay 2s)
- ✅ **Ollama retry mechanism:** Auto-retry untuk error 500/503 dengan 2 retries dan 2s delay (menangani transient errors seperti model loading, timeout)
- ✅ **Ollama model availability check:** Enhanced `isAvailable()` method yang memeriksa apakah model tersedia sebelum request
- ✅ Detailed error logging dengan status codes, error bodies, traces, user IDs
- ✅ Fallback values untuk AI responses yang gagal (default messages, empty arrays)
- ✅ Connection exception handling untuk network issues
- ✅ File validation untuk Stability AI image generation
- ✅ Response structure validation untuk RunwayML video generation

### Sale Mode System (2025-11-10):
- ✅ Complete Sale Mode System implementation
- ✅ Scarcity Mode: One-time purchase, resell capability, creator commission, grace period
- ✅ Standard Mode: Multiple sales, no resell, no commission, ownership stays with seller
- ✅ Repurchase flow dengan grace period & premium pricing
- ✅ Resale form dengan custom price setting
- ✅ Admin analytics dashboard dengan sale mode metrics
- ✅ Detailed repurchase report page
- ✅ Comprehensive test suite (unit & feature tests)
- ✅ Documentation seeder dengan 22 comprehensive entries

**Featured Notes System (2025-11-05):**
- ✅ Complete featured notes advertising system
- ✅ Landing page featured sections (hero & carousel)
- ✅ Marketplace featured banner & grid
- ✅ Popup modals (welcome, exit intent, interstitial)
- ✅ Seller analytics dashboard (impressions, clicks, CTR, ROI)
- ✅ Auto-approve untuk premium users
- ✅ Admin approval system dengan refund jika reject
- ✅ Auto-expire command untuk expired featured notes
- ✅ Analytics tracking (impressions & clicks)
- ✅ Pricing configurable per location & duration

**Security Hardening (2025-01-XX):**
- ✅ Security Headers Middleware (CSP, HSTS, X-Frame-Options, etc.)
- ✅ Rate Limiting Middleware untuk sensitive endpoints
- ✅ Input Sanitization Middleware
- ✅ Enhanced File Upload Security Service
  - Extension whitelist dan blacklist
  - MIME type validation
  - Magic bytes validation untuk images
  - Filename sanitization
  - Double extension detection
- ✅ Rate limiting untuk purchase, wallet, withdraw, resale, escrow, quote endpoints
- ✅ File upload security validation di StoreNoteRequest, UpdateNoteRequest, ProfileUpdateRequest
- ✅ Security configuration file (config/security.php)
- ✅ Security documentation (SECURITY.md)
- ✅ Fail2ban setup untuk SSH protection
- ✅ Automated backup scripts (database + storage)
- ✅ Health check monitoring
- ✅ Log rotation configuration
- ✅ Nginx security headers configuration

## 📋 Post-Deployment Checklist

Setelah semua setup selesai, pastikan checklist berikut sudah dikerjakan:

### Initial Setup
- [ ] Server requirements terinstall (PHP, MySQL, Nginx, Redis, Node.js, Composer)
- [ ] Database dibuat dan user dikonfigurasi
- [ ] Aplikasi di-clone dan dependencies terinstall
- [ ] Environment file (.env) dikonfigurasi dengan benar
- [ ] Migrations dan seeders dijalankan
- [ ] Storage link dibuat dan permissions di-set
- [ ] Laravel di-optimize (config, route, view cache)

### Security
- [ ] SSL certificate terinstall (Let's Encrypt)
- [ ] Security headers dikonfigurasi (Nginx + Middleware)
- [ ] Fail2ban dikonfigurasi untuk SSH protection
- [ ] Firewall (UFW) dikonfigurasi dan aktif
- [ ] Rate limiting di-test pada sensitive endpoints
- [ ] File upload security validation di-verify
- [ ] Input sanitization middleware aktif
- [ ] Security logging di-monitor
- [ ] `.env` file permissions: 600
- [ ] Storage directories permissions: 775/750
- [ ] PHP execution disabled di upload directories

### Services
- [ ] Nginx dikonfigurasi dan running
- [ ] PHP-FPM running
- [ ] MySQL running
- [ ] Redis running (jika digunakan)
- [ ] Queue workers (Supervisor) running
- [ ] Cron job untuk scheduler di-setup
- [ ] Ollama service running (jika menggunakan AI features)

### Payment Gateway
- [ ] Midtrans account terverifikasi (untuk production)
- [ ] Production keys dikonfigurasi di `.env`
- [ ] URL endpoints dikonfigurasi di Midtrans Dashboard
- [ ] Webhook endpoint di-test
- [ ] Payment flow di-test dengan test card

### Monitoring & Backup
- [ ] Laravel Telescope diakses dan berfungsi
- [ ] Log monitoring di-setup
- [ ] Health check script dibuat dan di-test
- [ ] Automated backup script dibuat
- [ ] Backup cronjob di-setup
- [ ] Backup restoration di-test (pada test database)

### Performance
- [ ] Redis cache dikonfigurasi (jika digunakan)
- [ ] CDN dikonfigurasi (jika digunakan)
- [ ] Image processing service di-setup (jika menggunakan intervention/image)
- [ ] Database indexes sudah ada (migration sudah dijalankan)
- [ ] OpCache enabled
- [ ] PHP-FPM tuning dikonfigurasi
- [ ] Nginx gzip compression enabled

### Testing
- [ ] User registration dan login di-test
- [ ] Note creation dan upload di-test
- [ ] Marketplace browsing dan search di-test
- [ ] Purchase flow di-test
- [ ] Wallet top-up di-test
- [ ] Withdraw request di-test
- [ ] Admin panel diakses dan berfungsi
- [ ] Scheduled tasks di-verify (subscription renewal, featured expire, etc.)

### Documentation
- [ ] Team members memiliki akses ke dokumentasi
- [ ] Deployment process didokumentasikan
- [ ] Backup dan restore procedures didokumentasikan
- [ ] Troubleshooting guide tersedia
- [ ] Security procedures didokumentasikan

## 🆘 Emergency Procedures

### Application Down

1. **Check Application Status:**
```bash
curl https://your-domain.com/up
sudo systemctl status php8.2-fpm
sudo systemctl status nginx
```

2. **Check Logs:**
```bash
sudo tail -f /var/www/noteds/storage/logs/laravel.log
sudo tail -f /var/log/nginx/error.log
```

3. **Restart Services:**
```bash
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
sudo supervisorctl restart noteds-worker:*
```

### Database Issues

1. **Check MySQL Status:**
```bash
sudo systemctl status mysql
mysql -u noteds_user -p noteds_production -e "SELECT 1"
```

2. **Restore from Backup:**
```bash
# Stop application
sudo systemctl stop php8.2-fpm

# Restore database
gunzip < /backups/noteds/db_YYYYMMDD_HHMMSS.sql.gz | mysql -u noteds_user -p noteds_production

# Start application
sudo systemctl start php8.2-fpm
```

### Disk Space Full

1. **Check Disk Usage:**
```bash
df -h
du -sh /var/www/noteds/storage/*
```

2. **Clean Up:**
```bash
# Clear Laravel caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Clear old logs (keep last 7 days)
find /var/www/noteds/storage/logs -name "*.log" -mtime +7 -delete

# Clear old backups (keep last 30 days)
find /backups/noteds -name "*.sql.gz" -mtime +30 -delete
```

### High Server Load

1. **Identify Resource Usage:**
```bash
htop
iotop
```

2. **Check Queue Workers:**
```bash
sudo supervisorctl status
# If stuck, restart:
sudo supervisorctl restart noteds-worker:*
```

3. **Check for Slow Queries:**
- Access Telescope: `/telescope/queries`
- Look for queries > 100ms

### Security Incident

1. **Immediate Actions:**
```bash
# Check recent security logs
sudo tail -f /var/www/noteds/storage/logs/laravel.log | grep -i "security\|upload\|rate.*limit"

# Check fail2ban status
sudo fail2ban-client status sshd

# Review recent file uploads
ls -lht /var/www/noteds/storage/app/public/uploads | head -20
```

2. **If Compromised:**
- Change all passwords immediately
- Review and revoke suspicious API keys
- Check for unauthorized database changes
- Restore from clean backup if necessary
- Review [SECURITY.md](SECURITY.md) for detailed procedures

## 🔧 PWA & Service Worker Setup

### PWA Manifest

PWA manifest sudah tersedia di `public/manifest.json`. Pastikan:

1. **Verify Manifest File:**
```bash
cat /var/www/noteds/public/manifest.json
```

2. **Update Manifest (if needed):**
- Edit `public/manifest.json` untuk update app name, icons, colors
- Icons harus tersedia di `public/icons/` directory
- Setelah update, clear cache: `php artisan optimize:clear`

### Service Worker

Service worker sudah tersedia di `public/sw.js`. Pastikan:

1. **Verify Service Worker:**
```bash
cat /var/www/noteds/public/sw.js
```

2. **Service Worker Registration:**
- Service worker otomatis di-register via JavaScript di `layouts/app.blade.php`
- Pastikan HTTPS aktif (service worker hanya bekerja di HTTPS)
- Test offline functionality setelah deployment

3. **Update Service Worker:**
- Jika update service worker, pastikan version number di-update
- Browser akan auto-update service worker pada visit berikutnya

### Dark Mode Configuration

Dark mode sudah diimplementasikan dengan:
- CSS variables untuk color management
- Tailwind CSS `dark:` classes
- LocalStorage untuk persistence
- Auto-detect system preference

**No additional server configuration needed** - dark mode bekerja sepenuhnya di client-side.

## 📱 Mobile Optimization

### Responsive Design
- ✅ Tailwind CSS responsive utilities
- ✅ Mobile-first approach
- ✅ Touch-friendly buttons dan interactions
- ✅ Optimized images dengan lazy loading

### PWA Features
- ✅ Installable sebagai aplikasi mobile
- ✅ Offline support via service worker
- ✅ App icons dan splash screens
- ✅ Full-screen experience

**No additional server configuration needed** - semua PWA features bekerja di client-side.

## 🔍 Additional Configuration Notes

### Environment Variables Summary

**Required for Production:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
DB_*=...
MIDTRANS_*=...
```

**Optional but Recommended:**
```env
REDIS_HOST=127.0.0.1
CDN_URL=https://cdn.yourdomain.com
TELESCOPE_ENABLED=true
SECURITY_CSP_ENABLED=true
SECURITY_SANITIZE_INPUT=true
```

**For AI Features (Optional):**
```env
OLLAMA_URL=http://localhost:11434
OLLAMA_MODEL=llama3.2
STABILITY_API_KEY=...
UNSPLASH_ACCESS_KEY=...
RUNWAY_API_KEY=...
```

### File Permissions Summary

```bash
# Application files
/var/www/noteds: 755 (owner: www-data)

# Storage directories
/var/www/noteds/storage: 775 (owner: www-data)
/var/www/noteds/storage/app/private: 750 (owner: www-data)
/var/www/noteds/storage/app/public: 775 (owner: www-data)

# Bootstrap cache
/var/www/noteds/bootstrap/cache: 775 (owner: www-data)

# Environment file
/var/www/noteds/.env: 600 (owner: www-data)

# Backup directory
/backups/noteds: 750 (owner: www-data)
```

### Cron Jobs Summary

```cron
# Laravel Scheduler (runs every minute)
* * * * * cd /var/www/noteds && php artisan schedule:run >> /dev/null 2>&1

# Database Backup (daily at 2 AM)
0 2 * * * /usr/local/bin/noteds-backup.sh >> /var/log/noteds-backup.log 2>&1

# Health Check (every 5 minutes)
*/5 * * * * /usr/local/bin/noteds-health-check.sh || echo "Health check failed" | mail -s "Noteds Health Alert" admin@your-domain.com

# S3 Backup (daily at 3 AM, if using S3)
0 3 * * * /usr/local/bin/noteds-s3-backup.sh >> /var/log/noteds-s3-backup.log 2>&1
```

### Service Status Commands

```bash
# Check all services
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql
sudo systemctl status redis-server
sudo systemctl status supervisor
sudo systemctl status ollama  # if using AI features

# Check queue workers
sudo supervisorctl status noteds-worker:*

# Check scheduled tasks
sudo -u www-data crontab -l

# Check application health
curl https://your-domain.com/up
```

### Useful Commands Reference

```bash
# Clear all caches
php artisan optimize:clear

# Optimize application
php artisan optimize

# Run migrations
php artisan migrate --force

# Run seeders
php artisan db:seed --force

# Check configuration
php artisan config:show

# Test database connection
php artisan db:show

# View routes
php artisan route:list

# Queue commands
php artisan queue:work
php artisan queue:restart

# Scheduled commands
php artisan schedule:list
php artisan schedule:run

# Telescope access
# Visit: https://your-domain.com/telescope (admin only)
```

## 📚 Additional Resources

### Documentation Files
- **[README.md](README.md)** - Project overview dan features
- **[LOCAL_SETUP.md](LOCAL_SETUP.md)** - Local development setup
- **[PRODUCTION_SETUP.md](PRODUCTION_SETUP.md)** - Production setup guide for Ubuntu & aaPanel (recommended)
- **[VPS_SETUP.md](VPS_SETUP.md)** - Manual VPS deployment guide
- **[TASKLIST.md](TASKLIST.md)** - Development roadmap dan task tracking
- **[PERFORMANCE_SETUP.md](PERFORMANCE_SETUP.md)** - Performance optimization guide
- **[SECURITY.md](SECURITY.md)** - Security hardening guide dan best practices
- **[FUTURE_FEATURES.md](FUTURE_FEATURES.md)** - Future features roadmap untuk membuat aplikasi sangat terkenal

### External Resources
- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Nginx Documentation](https://nginx.org/en/docs/)
- [MySQL 8.0 Documentation](https://dev.mysql.com/doc/refman/8.0/en/)
- [Redis Documentation](https://redis.io/documentation)
- [Let's Encrypt Documentation](https://letsencrypt.org/docs/)
- [Midtrans Documentation](https://docs.midtrans.com/)
- [Ollama Documentation](https://ollama.com/docs)

### Support & Community
- Laravel Community: [Laravel.io](https://laravel.io)
- Stack Overflow: Tag `laravel`, `nginx`, `mysql`
- GitHub Issues: Report bugs dan feature requests

---

**Last Updated:** 2025-01-XX  
**Version:** 1.0  
**Maintained by:** Noteds Development Team

---

## 🚀 Future Features & Roadmap

Untuk melihat daftar lengkap fitur-fitur tambahan yang akan membuat Noteds menjadi aplikasi marketplace yang sangat terkenal, lihat **[FUTURE_FEATURES.md](FUTURE_FEATURES.md)**.

Fitur-fitur yang direncanakan termasuk:
- 🟢 **High Priority:** Social features, gamification, mobile app, recommendation engine, video previews
- 🟡 **Medium Priority:** Advanced analytics, live chat, subscription plans, affiliate program, multi-language
- 🔵 **Low Priority:** Public API, community challenges, buyer protection, advanced workspace features

Semua fitur diprioritaskan berdasarkan impact pada user acquisition, retention, dan revenue.

