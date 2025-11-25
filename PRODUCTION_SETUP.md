# 🚀 Noteds - Production Setup Guide (Ubuntu & aaPanel)

Panduan lengkap untuk setup aplikasi Noteds di server Ubuntu dengan aaPanel untuk production environment.

## 📋 Prerequisites

- Ubuntu 22.04 LTS (recommended) atau Ubuntu 20.04 LTS
- Root access atau sudo privileges
- Domain name yang sudah diarahkan ke server IP
- Minimal 2GB RAM (4GB+ recommended)
- Minimal 20GB storage (50GB+ recommended untuk production)

## 🎯 Quick Start

### Step 1: Install aaPanel

```bash
# Download dan install aaPanel
wget -O install.sh http://www.aapanel.com/script/install-ubuntu_6.0_en.sh && sudo bash install.sh aapanel

# Set password untuk aaPanel (akan muncul setelah install)
# Catat password yang diberikan
```

**Access aaPanel:**
- URL: `http://your-server-ip:7800`
- Login dengan username dan password yang diberikan

### Step 2: Install Required Software via aaPanel

1. **Login ke aaPanel**
2. **App Store** → Install:
   - **Nginx** (latest version)
   - **MySQL 8.0** (atau MariaDB 10.11+)
   - **PHP 8.2** atau **PHP 8.3** (recommended: 8.3)
   - **Redis** (untuk caching)
   - **Node.js** (via Terminal atau App Store)
   - **Composer** (via Terminal)

3. **PHP Extensions** (via aaPanel → PHP → Install Extensions):
   - `php8.3-mysql` (atau sesuai versi PHP)
   - `php8.3-xml`
   - `php8.3-mbstring`
   - `php8.3-curl`
   - `php8.3-zip`
   - `php8.3-gd`
   - `php8.3-bcmath`
   - `php8.3-intl`
   - `php8.3-redis`
   - `php8.3-fileinfo`
   - `php8.3-tokenizer`
   - `php8.3-readline`

### Step 3: Create Database via aaPanel

1. **Database** → **Add Database**
2. Fill in:
   - **Database Name:** `noteds_production`
   - **Username:** `noteds_user`
   - **Password:** (generate strong password, save it!)
   - **Access Host:** `localhost`
3. Click **Submit**

### Step 4: Create Website via aaPanel

1. **Website** → **Add Site**
2. Fill in:
   - **Domain:** `your-domain.com` (dan `www.your-domain.com` jika perlu)
   - **Root Directory:** `/www/wwwroot/your-domain.com`
   - **PHP Version:** `PHP-8.3` (atau versi yang diinstall)
   - **Database:** `noteds_production`
   - **Database User:** `noteds_user`
   - **Database Password:** (password yang dibuat di step 3)
3. Click **Submit**

### Step 5: Deploy Application

```bash
# SSH ke server
ssh root@your-server-ip

# Navigate ke website directory
cd /www/wwwroot/your-domain.com

# Clone repository (atau upload via aaPanel Files)
git clone <your-repository-url> .

# Atau jika sudah ada, pull latest:
git pull origin main

# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Install NPM dependencies
npm install

# Build frontend assets
npm run build
```

### Step 6: Environment Configuration

1. **Via aaPanel Files Manager:**
   - Navigate ke `/www/wwwroot/your-domain.com`
   - Copy `.env.example` ke `.env`
   - Edit `.env` file

2. **Atau via SSH:**
```bash
cd /www/wwwroot/your-domain.com
cp .env.example .env
nano .env
```

**Update `.env` dengan konfigurasi berikut:**

```env
APP_NAME="Noteds"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database Configuration (dari aaPanel)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=noteds_production
DB_USERNAME=noteds_user
DB_PASSWORD=your_database_password_from_aapanel

# Application Key
# Generate dengan: php artisan key:generate
APP_KEY=base64:your-generated-key-here

# Midtrans Configuration
MIDTRANS_SERVER_KEY=your_midtrans_server_key
MIDTRANS_CLIENT_KEY=your_midtrans_client_key
MIDTRANS_IS_PRODUCTION=true
MIDTRANS_MERCHANT_ID=your_merchant_id

# Cache & Session
CACHE_STORE=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CLIENT=phpredis
REDIS_CACHE_DB=1

# Queue
QUEUE_CONNECTION=database

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# Security
SECURITY_CSP_ENABLED=true
SECURITY_SANITIZE_INPUT=true
FILE_UPLOAD_MAX_SIZE=10485760

# Telescope (Admin Only)
TELESCOPE_ENABLED=true
TELESCOPE_QUERY_WATCHER=true
TELESCOPE_SLOW_QUERY_THRESHOLD=100
```

**Generate Application Key:**
```bash
cd /www/wwwroot/your-domain.com
php artisan key:generate
```

### Step 7: Database Migration & Seeding

```bash
cd /www/wwwroot/your-domain.com

# Run migrations
php artisan migrate --force

# Seed database (initial data: admin user, settings, subscription plans, etc.)
php artisan db:seed --force
```

**Catatan:** Seeder akan membuat:
- Admin user (email: admin@noteds.com, password: password - **CHANGE IMMEDIATELY!**)
- Subscription plans (Basic, Pro, Enterprise)
- Initial settings
- Commission tiers
- Tax rules
- Dan data awal lainnya

### Step 8: Storage & Permissions Setup

```bash
cd /www/wwwroot/your-domain.com

# Create storage link
php artisan storage:link

# Set permissions
chmod -R 755 /www/wwwroot/your-domain.com
chmod -R 775 storage bootstrap/cache
chmod -R 750 storage/app/private
chmod 600 .env

# Set ownership (via aaPanel atau SSH)
chown -R www:www /www/wwwroot/your-domain.com
```

**Via aaPanel:**
1. **Files** → Navigate ke `/www/wwwroot/your-domain.com`
2. Right-click folder → **Permissions**
3. Set:
   - `storage`: 775
   - `bootstrap/cache`: 775
   - `storage/app/private`: 750
   - `.env`: 600
4. Set owner: `www:www`

### Step 9: Optimize Laravel

```bash
cd /www/wwwroot/your-domain.com

# Optimize Laravel (Laravel 12 combines all caches)
php artisan optimize

# Or manually:
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 10: Configure Nginx via aaPanel

1. **Website** → Click domain → **Settings**
2. **Configuration File** tab
3. Update configuration:

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /www/wwwroot/your-domain.com/public;
    index index.php index.html;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # File Upload Size (untuk file besar)
    client_max_body_size 200M;
    client_body_buffer_size 128k;
    client_body_timeout 900s;
    client_header_timeout 900s;
    send_timeout 900s;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss;
    gzip_comp_level 6;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/tmp/php-cgi-83.sock;  # Sesuaikan dengan versi PHP
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        
        # Timeout untuk file besar
        fastcgi_read_timeout 900;
        fastcgi_send_timeout 900;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Disable PHP execution in storage
    location ~ ^/storage/.*\.php$ {
        deny all;
    }
}
```

4. Click **Save**
5. **Reload** Nginx

### Step 11: Configure PHP via aaPanel

1. **App Store** → **PHP-8.3** → **Setting**
2. **Limit of upload** tab:
   - `upload_max_filesize`: **1000M**
   - `post_max_size`: **2000M**
   - `memory_limit`: **512M**
   - `max_execution_time`: **1000**
   - `max_input_time`: **900**
3. **Limit of timeout** tab:
   - `request_terminate_timeout`: **900**
4. **FPM profile** tab:
   - `request_terminate_timeout`: **900**
5. Click **Save**
6. **Service** → **Restart**

### Step 12: Setup SSL Certificate via aaPanel

1. **Website** → Click domain → **SSL**
2. Choose one:
   - **Let's Encrypt** (Free, recommended)
   - **Cloudflare SSL** (jika menggunakan Cloudflare)
   - **Other SSL** (jika punya certificate sendiri)
3. For **Let's Encrypt**:
   - Enter email
   - Select domains (your-domain.com, www.your-domain.com)
   - Click **Apply**
4. Enable **Force HTTPS** (redirect HTTP to HTTPS)

### Step 13: Setup Queue Workers (Supervisor)

**Via aaPanel Terminal atau SSH:**

```bash
# Install Supervisor (jika belum ada)
apt install supervisor -y

# Create supervisor config
nano /etc/supervisor/conf.d/noteds-worker.conf
```

**Add configuration:**

```ini
[program:noteds-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /www/wwwroot/your-domain.com/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www
numprocs=1
redirect_stderr=true
stdout_logfile=/www/wwwroot/your-domain.com/storage/logs/worker.log
stopwaitsecs=3600
```

**Start Supervisor:**

```bash
supervisorctl reread
supervisorctl update
supervisorctl start noteds-worker:*
```

**Check Status:**
```bash
supervisorctl status
```

### Step 14: Setup Cron Job (Scheduler)

**Via aaPanel:**

1. **Cron** → **Add Cron**
2. Fill in:
   - **Name:** Laravel Scheduler
   - **Type:** Shell Script
   - **Period:** N Minutes (1 minute)
   - **Script:**
   ```bash
   cd /www/wwwroot/your-domain.com && php artisan schedule:run >> /dev/null 2>&1
   ```
3. Click **Submit**

**Atau via SSH:**

```bash
crontab -e -u www

# Add:
* * * * * cd /www/wwwroot/your-domain.com && php artisan schedule:run >> /dev/null 2>&1
```

### Step 15: Configure Redis via aaPanel

1. **App Store** → **Redis** → **Setting**
2. **Service** → **Start** (jika belum running)
3. **Service** → **Set Boot** (auto-start on boot)

**Verify Redis:**
```bash
redis-cli ping
# Should return: PONG
```

### Step 16: Setup Firewall (UFW)

```bash
# Enable UFW
ufw enable

# Allow SSH (IMPORTANT - do this first!)
ufw allow 22/tcp

# Allow HTTP and HTTPS
ufw allow 80/tcp
ufw allow 443/tcp

# Allow aaPanel port (if needed)
ufw allow 7800/tcp

# Check status
ufw status
```

**Via aaPanel:**
1. **Security** → **Firewall**
2. Add rules:
   - Port 22 (SSH)
   - Port 80 (HTTP)
   - Port 443 (HTTPS)
   - Port 7800 (aaPanel, optional)

### Step 17: Configure Midtrans Payment Gateway

1. **Daftar Akun Midtrans:**
   - Kunjungi [https://dashboard.midtrans.com](https://dashboard.midtrans.com)
   - Daftar dan verifikasi akun (untuk production)

2. **Dapatkan Production Keys:**
   - Login ke Midtrans Dashboard
   - **Settings** → **Access Keys**
   - Copy **Production Server Key** dan **Production Client Key**
   - Update di `.env`:
   ```env
   MIDTRANS_SERVER_KEY=Mid-server-xxxxx
   MIDTRANS_CLIENT_KEY=Mid-client-xxxxx
   MIDTRANS_IS_PRODUCTION=true
   ```

3. **Konfigurasi URL Endpoints di Midtrans Dashboard:**
   - **Settings** → **Configuration** → **URL Settings**
   - **Payment Notification URL:** `https://your-domain.com/payment/callback`
   - **Finish Redirect URL:** `https://your-domain.com/payment/finish`
   - **Unfinish Redirect URL:** `https://your-domain.com/payment/unfinish`
   - **Error Redirect URL:** `https://your-domain.com/payment/error`

4. **Clear Laravel Cache:**
   ```bash
   cd /www/wwwroot/your-domain.com
   php artisan config:clear
   php artisan cache:clear
   php artisan optimize
   ```

### Step 18: Initial Admin Setup

1. **Login ke aplikasi:**
   - URL: `https://your-domain.com/login`
   - Email: `admin@noteds.com` (default dari seeder)
   - Password: `password` (default dari seeder)

2. **CHANGE PASSWORD IMMEDIATELY!**
   - Profile → Change Password
   - Set strong password

3. **Update Admin Settings:**
   - Admin Panel → Settings
   - Update:
     - Site name
     - Support email
     - Commission rates
     - Tax rules
     - Featured notes pricing
     - Dan settings lainnya

### Step 19: Verify Installation

**Check Application Health:**
```bash
curl https://your-domain.com/up
# Should return: {"status":"ok"}
```

**Check Services:**
```bash
# Nginx
systemctl status nginx

# PHP-FPM
systemctl status php-fpm-83

# MySQL
systemctl status mysql

# Redis
systemctl status redis

# Supervisor
systemctl status supervisor
supervisorctl status
```

**Check Logs:**
```bash
# Laravel logs
tail -f /www/wwwroot/your-domain.com/storage/logs/laravel.log

# Nginx logs
tail -f /www/wwwlogs/your-domain.com.log

# Queue worker logs
tail -f /www/wwwroot/your-domain.com/storage/logs/worker.log
```

### Step 20: Setup Automated Backups

**Via aaPanel:**

1. **Files** → **Backup**
2. **Add Backup Task**
3. Configure:
   - **Backup Type:** Database + Files
   - **Database:** `noteds_production`
   - **Backup Path:** `/www/backup/noteds`
   - **Backup Cycle:** Daily
   - **Retention:** 30 days
4. Click **Submit**

**Atau via SSH (Manual Script):**

```bash
# Create backup script
nano /usr/local/bin/noteds-backup.sh
```

**Add:**

```bash
#!/bin/bash

BACKUP_DIR="/www/backup/noteds"
DB_USER="noteds_user"
DB_NAME="noteds_production"
DB_PASS=$(grep DB_PASSWORD /www/wwwroot/your-domain.com/.env | cut -d '=' -f2)
RETENTION_DAYS=30

mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$(date +%Y%m%d_%H%M%S).sql.gz

# Storage backup
tar -czf $BACKUP_DIR/storage_$(date +%Y%m%d_%H%M%S).tar.gz \
    -C /www/wwwroot/your-domain.com \
    --exclude='storage/framework/cache' \
    --exclude='storage/framework/sessions' \
    --exclude='storage/framework/views' \
    --exclude='storage/logs' \
    storage/app

# Remove old backups
find $BACKUP_DIR -type f -name "*.sql.gz" -mtime +$RETENTION_DAYS -delete
find $BACKUP_DIR -type f -name "*.tar.gz" -mtime +$RETENTION_DAYS -delete

echo "Backup completed: $(date)"
```

**Make executable:**
```bash
chmod +x /usr/local/bin/noteds-backup.sh
```

**Add to Cron:**
```bash
crontab -e

# Add (daily at 2 AM):
0 2 * * * /usr/local/bin/noteds-backup.sh >> /var/log/noteds-backup.log 2>&1
```

## 🔄 Deployment Workflow

### Manual Deployment

```bash
cd /www/wwwroot/your-domain.com

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Run migrations
php artisan migrate --force

# Clear and optimize
php artisan optimize:clear
php artisan optimize

# Restart queue workers
supervisorctl restart noteds-worker:*

# Reload PHP-FPM
systemctl reload php-fpm-83
```

### Automated Deployment (GitHub Actions)

Create `.github/workflows/deploy.yml`:

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
          cd /www/wwwroot/your-domain.com
          git pull origin main
          composer install --no-dev --optimize-autoloader
          npm install
          npm run build
          php artisan migrate --force
          php artisan optimize
          supervisorctl restart noteds-worker:*
          systemctl reload php-fpm-83
```

## 🚨 Troubleshooting

### 413 Request Entity Too Large

**Via aaPanel:**
1. **App Store** → **Nginx** → **Setting** → **Configuration File**
2. Update domain config, add:
   ```nginx
   client_max_body_size 200M;
   ```
3. **App Store** → **PHP-8.3** → **Setting** → **Limit of upload**
4. Set:
   - `upload_max_filesize`: 1000M
   - `post_max_size`: 2000M
5. Restart services

### Queue Not Processing

```bash
# Check supervisor status
supervisorctl status

# Restart workers
supervisorctl restart noteds-worker:*

# Check logs
tail -f /www/wwwroot/your-domain.com/storage/logs/worker.log
```

### Database Connection Issues

1. **Check database credentials in `.env`**
2. **Test connection via aaPanel:**
   - **Database** → Click database → **Manage**
   - Try to connect
3. **Check MySQL service:**
   ```bash
   systemctl status mysql
   ```

### Assets Not Loading

```bash
cd /www/wwwroot/your-domain.com

# Rebuild assets
npm run build

# Clear cache
php artisan optimize:clear
php artisan optimize
```

### Permission Issues

```bash
# Fix ownership
chown -R www:www /www/wwwroot/your-domain.com

# Fix permissions
chmod -R 755 /www/wwwroot/your-domain.com
chmod -R 775 storage bootstrap/cache
chmod 600 .env
```

## 📊 Monitoring

### Laravel Telescope

- URL: `https://your-domain.com/telescope`
- Access: Admin users only
- Monitor: Queries, requests, jobs, exceptions

### Log Monitoring

**Via aaPanel:**
- **Files** → `/www/wwwroot/your-domain.com/storage/logs/`

**Via SSH:**
```bash
# Laravel logs
tail -f /www/wwwroot/your-domain.com/storage/logs/laravel.log

# Nginx logs
tail -f /www/wwwlogs/your-domain.com.log

# Queue worker logs
tail -f /www/wwwroot/your-domain.com/storage/logs/worker.log
```

## 🔒 Security Checklist

- [ ] `APP_DEBUG=false` in `.env`
- [ ] Strong database password
- [ ] SSL/HTTPS enabled
- [ ] Firewall (UFW) configured
- [ ] File permissions set correctly
- [ ] `.env` file permissions: 600
- [ ] Security headers configured in Nginx
- [ ] Rate limiting active
- [ ] Automated backups configured
- [ ] Admin password changed from default
- [ ] Midtrans production keys configured
- [ ] Webhook endpoints configured in Midtrans Dashboard

## 📝 Additional Notes

### Subscription Plans

Seeder akan membuat 3 subscription plans:
- **Basic:** $5/month, $50/year (17% discount)
- **Pro:** $15/month, $150/year (17% discount)
- **Enterprise:** $50/month, $500/year (17% discount)

Plans dapat di-manage via Admin Panel setelah deployment.

### Scheduled Tasks

Laravel scheduler akan otomatis menjalankan:
- **Subscription Renewal:** Daily at 00:00 WIB
- **Featured Notes Expiry:** Daily at 01:00 WIB
- **Forum Scheduled Posts:** Every minute

Pastikan cron job sudah di-setup (Step 14).

### Performance Optimization

- Redis caching enabled (auto-detect)
- OpCache enabled (PHP default)
- Gzip compression (Nginx)
- Database indexes (via migrations)
- CDN support (optional, configure in `.env`)

## 🆘 Support

Untuk masalah deployment:
- Check logs: `/www/wwwroot/your-domain.com/storage/logs/laravel.log`
- Check aaPanel logs: `/www/server/panel/logs/`
- Review [VPS_SETUP.md](VPS_SETUP.md) untuk detail lebih lanjut
- Review [SECURITY.md](SECURITY.md) untuk security best practices

---

**Last Updated:** 2025-11-25  
**Version:** 1.0  
**Maintained by:** Noteds Development Team

