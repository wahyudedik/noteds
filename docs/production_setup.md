# Production Setup di aaPanel

Panduan lengkap untuk setup Laravel application di production environment menggunakan aaPanel.

## Prerequisites

- Server dengan aaPanel terinstall
- Domain name sudah dikonfigurasi
- SSH access ke server
- Basic knowledge tentang Linux commands

## 1. Server Requirements

### 1.1 Minimum Requirements

- **OS**: CentOS 7+, Ubuntu 18.04+, Debian 9+
- **RAM**: Minimum 2GB (Recommended: 4GB+)
- **CPU**: 2 cores minimum
- **Storage**: 20GB+ free space
- **PHP**: 8.1 atau 8.2
- **MySQL/MariaDB**: 5.7+ atau 10.3+
- **Nginx/Apache**: Latest stable version

### 1.2 Check Current Setup

```bash
# Check PHP version
php -v

# Check MySQL version
mysql --version

# Check disk space
df -h

# Check memory
free -m
```

## 2. Install Required Software via aaPanel

### 2.1 Install PHP Extensions

1. Login ke aaPanel
2. Navigasi ke **App Store** → **PHP**
3. Install PHP 8.1 atau 8.2
4. Klik **Settings** → **Install Extensions**:
   - `php-fpm`
   - `php-mysql`
   - `php-mbstring`
   - `php-xml`
   - `php-curl`
   - `php-zip`
   - `php-gd`
   - `php-redis` (jika menggunakan Redis)
   - `php-bcmath`
   - `php-fileinfo`
   - `php-opcache`

### 2.2 Install Composer

Via aaPanel Terminal atau SSH:

```bash
# Download Composer
cd /tmp
curl -sS https://getcomposer.org/installer | php

# Move to global location
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Verify installation
composer --version
```

### 2.3 Install Node.js & NPM

Via aaPanel:
1. **App Store** → **Node.js Version Manager**
2. Install Node.js 18.x atau 20.x LTS

Atau via SSH:
```bash
# Install Node.js via NVM
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
source ~/.bashrc
nvm install 20
nvm use 20
```

## 3. Setup Website di aaPanel

### 3.1 Create Website

1. Login ke aaPanel
2. Navigasi ke **Website** → **Add Site**
3. Fill in:
   - **Domain**: your-domain.com
   - **Root Directory**: `/www/wwwroot/your-domain.com`
   - **PHP Version**: PHP 8.1 atau 8.2
   - **Database**: Create MySQL database
   - **FTP**: Optional (create jika perlu)

### 3.2 Configure PHP Settings

1. **Website** → Pilih website → **Settings** → **PHP Settings**
2. Update `php.ini`:

```ini
memory_limit = 256M
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
max_input_time = 300
```

3. Enable OPcache:

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### 3.3 Configure Nginx/Apache

**Nginx Configuration** (Recommended):

Edit file: `/www/server/panel/vhost/nginx/your-domain.com.conf`

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /www/wwwroot/your-domain.com/public;
    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/tmp/php-cgi-81.sock;  # Sesuaikan dengan PHP version
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;
}
```

**Apache Configuration** (Alternative):

Edit file: `/www/server/panel/vhost/apache/your-domain.com.conf`

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    DocumentRoot /www/wwwroot/your-domain.com/public

    <Directory /www/wwwroot/your-domain.com/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

## 4. Deploy Application

### 4.1 Upload Files

**Option 1: Via Git** (Recommended)

```bash
cd /www/wwwroot/your-domain.com
git clone https://github.com/your-repo/noteds.git .
# atau jika sudah ada, pull latest
git pull origin main
```

**Option 2: Via FTP/SFTP**

1. Upload files via aaPanel File Manager atau FTP client
2. Extract jika dalam format zip

**Option 3: Via aaPanel File Manager**

1. **Files** → Navigate to website directory
2. Upload files via web interface

### 4.2 Set Permissions

```bash
cd /www/wwwroot/your-domain.com

# Set ownership
chown -R www:www .

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Set special permissions untuk storage dan cache
chmod -R 775 storage bootstrap/cache
chown -R www:www storage bootstrap/cache
```

### 4.3 Install Dependencies

```bash
cd /www/wwwroot/your-domain.com

# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node dependencies
npm install

# Build assets
npm run build
# atau
npm run production
```

### 4.4 Setup Environment

```bash
# Copy .env.example to .env
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit .env file
nano .env
```

**Update .env dengan production values**:

```env
APP_NAME="Noteds"
APP_ENV=production
APP_KEY=base64:...  # Generated by key:generate
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 4.5 Run Migrations

```bash
# Run migrations
php artisan migrate --force

# Seed database (jika perlu)
php artisan db:seed --force
```

### 4.6 Setup Storage Link

```bash
# Create symbolic link untuk storage
php artisan storage:link
```

## 5. Optimize Application

### 5.1 Cache Configuration

```bash
# Cache config
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache events
php artisan event:cache
```

### 5.2 Optimize Autoloader

```bash
composer install --optimize-autoloader --no-dev
```

### 5.3 Setup Queue & Scheduler

Lihat dokumentasi `setup_queue_schedule.md` untuk detail lengkap.

**Quick Setup**:

```bash
# Setup supervisor untuk queue worker
# (lihat setup_queue_schedule.md)

# Setup cron untuk scheduler
crontab -e
# Tambahkan:
* * * * * cd /www/wwwroot/your-domain.com && php artisan schedule:run >> /dev/null 2>&1
```

## 6. SSL Certificate Setup

### 6.1 Install SSL via aaPanel

1. **Website** → Pilih website → **Settings** → **SSL**
2. Pilih **Let's Encrypt** (Free SSL)
3. Fill in email address
4. Click **Apply**
5. Enable **Force HTTPS**

### 6.2 Manual SSL Setup (Alternative)

```bash
# Install certbot
yum install certbot python3-certbot-nginx -y  # CentOS
apt-get install certbot python3-certbot-nginx -y  # Ubuntu

# Generate certificate
certbot --nginx -d your-domain.com -d www.your-domain.com

# Auto-renewal (already setup by certbot)
```

## 7. Security Hardening

### 7.1 Firewall Configuration

Via aaPanel:
1. **Security** → **Firewall**
2. Allow ports: 80, 443, 22 (SSH)
3. Block all other ports

Via SSH:
```bash
# UFW (Ubuntu)
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 22/tcp
ufw enable

# Firewalld (CentOS)
firewall-cmd --permanent --add-service=http
firewall-cmd --permanent --add-service=https
firewall-cmd --permanent --add-service=ssh
firewall-cmd --reload
```

### 7.2 File Permissions

```bash
# Restrict .env file
chmod 600 /www/wwwroot/your-domain.com/.env

# Restrict storage
chmod -R 775 /www/wwwroot/your-domain.com/storage
chown -R www:www /www/wwwroot/your-domain.com/storage
```

### 7.3 Disable Directory Listing

Nginx config sudah include `deny all` untuk hidden files.

Apache: Pastikan `.htaccess` ada di public directory.

### 7.4 Update .env Security

```env
APP_DEBUG=false
APP_ENV=production
LOG_LEVEL=error
```

## 8. Database Setup

### 8.1 Create Database via aaPanel

1. **Database** → **Add Database**
2. Fill in:
   - **Database Name**: your_database_name
   - **Username**: your_database_user
   - **Password**: Strong password
3. Click **Submit**

### 8.2 Import Database (jika ada)

```bash
# Via aaPanel: Database → Import
# atau via SSH:
mysql -u username -p database_name < backup.sql
```

### 8.3 Database Optimization

```sql
-- Optimize tables
OPTIMIZE TABLE table_name;

-- Check table status
SHOW TABLE STATUS;
```

## 9. Monitoring & Logs

### 9.1 Setup Log Rotation

Create `/etc/logrotate.d/laravel`:

```
/www/wwwroot/your-domain.com/storage/logs/*.log {
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www www
    sharedscripts
    postrotate
        /usr/bin/supervisorctl restart laravel-worker:* > /dev/null 2>&1 || true
    endscript
}
```

### 9.2 Monitor Application

```bash
# Check Laravel logs
tail -f /www/wwwroot/your-domain.com/storage/logs/laravel.log

# Check Nginx logs
tail -f /www/wwwroot/wwwlogs/your-domain.com.log

# Check PHP-FPM logs
tail -f /www/server/php/81/var/log/php-fpm.log

# Check system resources
htop
df -h
free -m
```

### 9.3 Setup Monitoring (Optional)

- **Sentry**: Error tracking
- **New Relic**: Application performance monitoring
- **Uptime Robot**: Uptime monitoring

## 10. Backup Strategy

### 10.1 Database Backup

Via aaPanel:
1. **Database** → Select database → **Backup**
2. Setup automatic backup schedule

Via SSH (Automated):
```bash
# Create backup script
nano /root/backup-db.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/database"
mkdir -p $BACKUP_DIR

mysqldump -u username -p'password' database_name > $BACKUP_DIR/db_$DATE.sql
gzip $BACKUP_DIR/db_$DATE.sql

# Keep only last 7 days
find $BACKUP_DIR -name "*.sql.gz" -mtime +7 -delete
```

```bash
chmod +x /root/backup-db.sh

# Add to crontab (daily at 2 AM)
crontab -e
0 2 * * * /root/backup-db.sh
```

### 10.2 Files Backup

```bash
# Backup storage and uploads
tar -czf /backups/files/files_$(date +%Y%m%d).tar.gz /www/wwwroot/your-domain.com/storage/app
```

## 11. Performance Optimization

### 11.1 Enable OPcache

Already configured in PHP settings (see section 2.2).

### 11.2 Enable Redis Caching

```bash
# Install Redis via aaPanel: App Store → Redis
# atau via SSH:
yum install redis -y
systemctl start redis
systemctl enable redis
```

Update `.env`:
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 11.3 CDN Setup (Optional)

- Cloudflare: Free CDN and DDoS protection
- AWS CloudFront: Paid CDN service
- Configure in `.env`:
```env
ASSET_URL=https://cdn.your-domain.com
```

## 12. Troubleshooting

### 12.1 500 Internal Server Error

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check PHP errors
tail -f /www/server/php/81/var/log/php-fpm.log

# Check permissions
ls -la storage bootstrap/cache
```

### 12.2 Database Connection Error

```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check .env database config
cat .env | grep DB_
```

### 12.3 Permission Denied

```bash
# Fix ownership
chown -R www:www /www/wwwroot/your-domain.com

# Fix permissions
chmod -R 755 /www/wwwroot/your-domain.com
chmod -R 775 storage bootstrap/cache
```

### 12.4 Queue Not Working

See `setup_queue_schedule.md` for detailed troubleshooting.

## 13. Production Checklist

- [ ] Server requirements met
- [ ] PHP extensions installed
- [ ] Composer installed
- [ ] Node.js & NPM installed
- [ ] Website created in aaPanel
- [ ] PHP settings optimized
- [ ] Nginx/Apache configured
- [ ] Application deployed
- [ ] Permissions set correctly
- [ ] Dependencies installed
- [ ] .env configured
- [ ] Database migrated
- [ ] Storage link created
- [ ] Application optimized (cache)
- [ ] SSL certificate installed
- [ ] Firewall configured
- [ ] Queue worker running
- [ ] Scheduler configured
- [ ] Backup strategy implemented
- [ ] Monitoring setup
- [ ] Log rotation configured
- [ ] Security hardened

## 14. Maintenance Commands

```bash
# Clear all caches
php artisan optimize:clear

# Re-optimize
php artisan optimize

# Update dependencies
composer update --no-dev
npm update
npm run build

# Run migrations
php artisan migrate --force

# Check application status
php artisan about
```

## 15. Additional Resources

- [Laravel Deployment Documentation](https://laravel.com/docs/deployment)
- [aaPanel Documentation](https://doc.aapanel.com/)
- [Nginx Documentation](https://nginx.org/en/docs/)
- [PHP-FPM Documentation](https://www.php.net/manual/en/install.fpm.php)

---

**Last Updated**: 2025-01-XX
**Maintained by**: Development Team
