# 🌐 Noteds — VPS Deployment Guide

## 📋 Server Requirements

- Ubuntu 22.04 LTS (recommended)
- Nginx
- PHP 8.2+ FPM
- MySQL 8.0+
- Node.js 18+ / NPM
- Composer 2.x
- Supervisor (for queue workers)
- Redis (optional, for caching)

## 🚀 Deployment Steps

### 1. Server Preparation

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2-fpm php8.2-cli php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl -y

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

MIDTRANS_SERVER_KEY=your_production_key
MIDTRANS_CLIENT_KEY=your_production_key
MIDTRANS_IS_PRODUCTION=true

QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_DRIVER=file
```

### 5. Run Migrations & Seeders

```bash
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan db:seed --force
```

### 6. Storage & Permissions

```bash
sudo -u www-data php artisan storage:link
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

### 7. Optimize Laravel

```bash
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

### 8. Nginx Configuration

Create `/etc/nginx/sites-available/noteds`:
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/noteds/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

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
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/noteds /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 9. SSL Certificate (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d your-domain.com
```

### 10. Queue Workers (Supervisor)

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

### 11. Scheduled Tasks (Cron)

Edit crontab:
```bash
sudo crontab -e -u www-data
```

Add:
```cron
* * * * * cd /var/www/noteds && php artisan schedule:run >> /dev/null 2>&1
```

### 12. Monitoring Setup

#### Laravel Telescope (Production Admin Only)
Already configured in `TelescopeServiceProvider` with admin role gate.

#### Sentry (Error Tracking)
```bash
composer require sentry/sentry-laravel
```

Configure in `.env`:
```env
SENTRY_LARAVEL_DSN=your_sentry_dsn
SENTRY_TRACES_SAMPLE_RATE=1.0
```

## 🔄 Deployment Workflow

### Manual Deployment
```bash
cd /var/www/noteds
sudo git pull origin main
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data php artisan migrate --force
sudo -u www-data npm run build
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo supervisorctl restart noteds-worker:*
```

### Automated Deployment (GitHub Actions)

See `.github/workflows/deploy.yml` (create if needed)

## 🔒 Security Checklist

- [ ] Set `APP_DEBUG=false`
- [ ] Use strong database passwords
- [ ] Enable SSL/HTTPS
- [ ] Configure firewall (UFW)
- [ ] Set proper file permissions (775/664)
- [ ] Disable PHP execution in storage
- [ ] Regular security updates
- [ ] Database backups automated
- [ ] Rate limiting configured
- [ ] Email notification for errors

## 🔄 Backup Strategy

### Database Backup
```bash
# Manual backup
mysqldump -u noteds_user -p noteds_production > backup_$(date +%Y%m%d).sql

# Automated backup (cronjob)
0 2 * * * mysqldump -u noteds_user -pPassword noteds_production | gzip > /backups/noteds_$(date +\%Y\%m\%d).sql.gz
```

### File Storage Backup
```bash
# Sync to S3 or remote storage
aws s3 sync /var/www/noteds/storage/app s3://your-bucket/storage --delete
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

### Redis Caching (Optional)
```bash
sudo apt install redis-server -y
```

Update `.env`:
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## 🚨 Troubleshooting

### 500 Internal Server Error
- Check `storage/logs/laravel.log`
- Verify file permissions
- Run `php artisan config:clear`

### Queue Not Processing
```bash
sudo supervisorctl status
sudo supervisorctl restart noteds-worker:*
```

### Database Connection Issues
- Verify `.env` credentials
- Check MySQL service: `sudo systemctl status mysql`
- Test connection: `mysql -u noteds_user -p noteds_production`

### Assets Not Loading
- Run `npm run build` in production
- Check Nginx config for proper root
- Verify file permissions

## 📞 Support

For deployment issues, refer to:
- [Laravel Deployment Docs](https://laravel.com/docs/12.x/deployment)
- [DigitalOcean Laravel Setup](https://www.digitalocean.com/community/tutorials/how-to-install-and-configure-laravel-with-nginx-on-ubuntu-22-04)

