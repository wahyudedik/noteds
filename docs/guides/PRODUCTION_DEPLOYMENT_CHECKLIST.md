# 🚀 Production Security Deployment Checklist

This guide ensures the Noteds application is deployed with enterprise-grade security.

## Pre-Deployment Verification

### 1. Environment Variables (.env)
```bash
# ✓ REQUIRED: Never commit .env to version control
APP_ENV=production
APP_DEBUG=false                    # CRITICAL: Disable debug mode
APP_KEY=base64:xxxxx              # Generate with php artisan key:generate

# Database
DB_HOST=localhost
DB_DATABASE=noteds_prod
DB_USERNAME=noteds_user           # Use dedicated low-privilege user
DB_PASSWORD=strong_random_password # Use strong, random password

# Security
APP_URL=https://noteds.app        # HTTPS only
SECURE_HEADERS=true               # Enable security headers
ENCRYPTION_KEY=xxxxx              # Use app key

# Session
SESSION_SECURE_COOKIES=true       # HTTPS only
SESSION_HTTP_ONLY=true            # No JavaScript access
SESSION_SAME_SITE=lax             # CSRF protection

# File Storage
FILESYSTEM_DISK=s3                # Use S3 or encrypted storage
AWS_ACCESS_KEY_ID=xxxxx
AWS_SECRET_ACCESS_KEY=xxxxx
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=noteds-files

# Mail (for audit alerts)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=xxxxx
MAIL_PASSWORD=xxxxx

# Rate Limiting
RATE_LIMIT_PER_MINUTE=60

# Monitoring
SENTRY_LARAVEL_DSN=https://xxxxx@sentry.io/xxxxx
LOG_CHANNEL=stack
LOG_LEVEL=warning
```

### 2. Database Security

```bash
# Create dedicated database user with limited permissions
CREATE USER 'noteds_user'@'localhost' IDENTIFIED BY 'strong_password';

# Grant only necessary privileges
GRANT SELECT, INSERT, UPDATE, DELETE ON noteds_prod.* TO 'noteds_user'@'localhost';

# Revoke dangerous privileges
REVOKE FILE, SUPER, CREATE, DROP ON *.* FROM 'noteds_user'@'localhost';

# Apply changes
FLUSH PRIVILEGES;

# Enable encryption at rest (MySQL 8.0+)
ALTER TABLE audit_logs ENCRYPTION='Y';
ALTER TABLE api_tokens ENCRYPTION='Y';
ALTER TABLE users ENCRYPTION='Y';
```

### 3. SSL/TLS Certificate

```bash
# Use Let's Encrypt with Certbot
sudo certbot certonly --webroot -w /var/www/noteds/public -d noteds.app

# Nginx configuration
server {
    listen 443 ssl http2;
    server_name noteds.app;

    # SSL certificates
    ssl_certificate /etc/letsencrypt/live/noteds.app/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/noteds.app/privkey.pem;

    # Strong SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name noteds.app;
    return 301 https://$server_name$request_uri;
}
```

### 4. Web Server Configuration

#### Nginx
```nginx
# Prevent exposure of sensitive files
location ~ /\. {
    deny all;
}

location ~ /\.env {
    deny all;
}

location ~ /storage/ {
    deny all;
}

location ~ /bootstrap/ {
    deny all;
}

# Restrict script execution in upload directory
location ~ ^/storage/uploads/ {
    location ~ \.php$ {
        deny all;
    }
}

# PHP-FPM configuration
upstream php {
    server unix:/run/php/php8.4-fpm.sock;
}

location ~ \.php$ {
    fastcgi_pass php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
    
    # Security
    fastcgi_param HTTP_X_FORWARDED_FOR $remote_addr;
    fastcgi_param REMOTE_ADDR $remote_addr;
}
```

#### Apache
```apache
<VirtualHost *:443>
    ServerName noteds.app
    DocumentRoot /var/www/noteds/public

    # Disable directory listing
    <Directory /var/www/noteds/public>
        Options -Indexes
        AllowOverride All
        Require all granted

        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteBase /
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^ index.php [QSA,L]
        </IfModule>
    </Directory>

    # Prevent access to sensitive files
    <FilesMatch "\.(env|json|config|sqlite)$">
        Deny from all
    </FilesMatch>

    # SSL configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/noteds.app/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/noteds.app/privkey.pem
    SSLProtocol TLSv1.2 TLSv1.3
</VirtualHost>
```

### 5. File Permissions

```bash
# Application owner
sudo chown -R www-data:www-data /var/www/noteds

# Directories: 755, Files: 644
find /var/www/noteds -type f -exec chmod 644 {} \;
find /var/www/noteds -type d -exec chmod 755 {} \;

# Writable directories
chmod 775 /var/www/noteds/storage
chmod 775 /var/www/noteds/bootstrap/cache

# Sensitive files: read-only
chmod 400 /var/www/noteds/.env
chmod 400 /var/www/noteds/.env.production

# Hide .env from web
chmod 000 /var/www/noteds/.env.example
```

### 6. Firewall Configuration

```bash
# UFW (Ubuntu Firewall)
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Allow SSH (critical for access)
sudo ufw allow 22/tcp

# Allow HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable

# Check rules
sudo ufw status
```

### 7. Database Backups

```bash
# Daily encrypted backups
0 2 * * * mysqldump -u noteds_user -p'password' noteds_prod | gzip | gpg --encrypt --recipient admin@noteds.app > /backup/noteds_$(date +\%Y\%m\%d).sql.gz.gpg

# Weekly full backup
0 3 * * 0 /bin/bash /scripts/full_backup.sh

# Store offsite
0 4 * * * aws s3 sync /backup/ s3://noteds-backups/ --sse AES256
```

### 8. Monitoring & Logging

```bash
# Application logs
tail -f storage/logs/laravel.log

# Security logs
tail -f storage/logs/security.log

# Web server logs
tail -f /var/log/nginx/error.log
tail -f /var/log/nginx/access.log

# System logs
tail -f /var/log/syslog
tail -f /var/log/auth.log

# Monitor with ELK Stack or Sentry
# Configure in config/logging.php
```

### 9. DDoS Protection

```bash
# Cloudflare configuration (recommended)
# 1. Add DNS records to Cloudflare
# 2. Enable DDoS protection: Settings > Security
# 3. Configure rate limiting: Firewall > Rate limiting rules
# 4. Enable Web Application Firewall

# Alternative: ModSecurity
sudo apt install modsecurity modsecurity-apache

# Configure ModSecurity rules
sudo cp /etc/modsecurity/modsecurity.conf-recommended /etc/modsecurity/modsecurity.conf
sudo nano /etc/modsecurity/modsecurity.conf
```

### 10. Intrusion Detection

```bash
# Install fail2ban
sudo apt install fail2ban

# Configuration for Laravel
sudo nano /etc/fail2ban/jail.local

[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[sshd]
enabled = true

[nginx-http-auth]
enabled = true

[nginx-limit-req]
enabled = true

# Monitor
sudo fail2ban-client status
```

### 11. Regular Security Updates

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Update PHP dependencies
cd /var/www/noteds
composer update

# Check for vulnerabilities
composer audit

# Review security advisories
php artisan security:audit
```

### 12. Testing Security

```bash
# Run security test suite
php artisan test --filter=Security

# Check for OWASP Top 10 issues
./vendor/bin/phpstan analyse --level=max

# Scan with RIPS (for PHP)
# https://www.rips.io/

# Test SSL configuration
# https://www.ssllabs.com/ssltest/

# Check headers
# https://securityheaders.com/

# Verify HTTPS
curl -I https://noteds.app
```

## Post-Deployment

### 1. Run Database Migrations
```bash
cd /var/www/noteds
php artisan migrate --force
php artisan db:seed --class=AdminSeeder # Create admin user
```

### 2. Generate API Tokens
```bash
php artisan tinker

# Create API token for integrations
$user = User::where('role', 'system')->first();
$token = bin2hex(random_bytes(32));
$user->apiTokens()->create([
    'name' => 'System Integration Token',
    'token' => hash('sha256', $token),
    'scopes' => ['api.read', 'api.write'],
]);

echo $token; // Display to securely transfer
```

### 3. Setup Monitoring
```bash
# Configure Sentry for error tracking
# Configure New Relic for APM
# Setup Datadog for infrastructure monitoring
# Configure PagerDuty for alerting
```

### 4. Initial Security Audit

```bash
# Verify all security measures
php artisan security:audit

# Check audit logs for any issues
tail -f storage/logs/security.log

# Review user access logs
php artisan audit:report --action=login --days=7
```

### 5. Team Access Setup

```bash
# Create admin accounts
php artisan user:create --role=admin

# Setup SSH keys (disable password auth)
ssh-copy-id user@server

# Setup backup access keys
# Rotate keys monthly
```

## Ongoing Maintenance

### Weekly
- [ ] Review security logs
- [ ] Check for failed login attempts
- [ ] Monitor API usage
- [ ] Check disk space

### Monthly
- [ ] Run full security audit
- [ ] Review user permissions
- [ ] Update dependencies
- [ ] Rotate API tokens
- [ ] Review database backups

### Quarterly
- [ ] Penetration testing
- [ ] Update security policies
- [ ] Team security training
- [ ] Review audit logs for patterns

### Annually
- [ ] Full security assessment
- [ ] Update SSL certificates
- [ ] Review and update security architecture
- [ ] Compliance audit (if applicable)

## Emergency Response

### If Breach Suspected
```bash
# 1. Isolate affected systems
sudo systemctl stop nginx
sudo systemctl stop php8.4-fpm

# 2. Preserve logs
cp storage/logs/* /secure/backup/

# 3. Check for malware
sudo rkhunter --check --skip-keypress
sudo chkrootkit

# 4. Review recent changes
git log --all --oneline -20
ls -la storage/logs/

# 5. Notify affected users
php artisan notify:breach

# 6. Begin incident response
```

## Compliance Requirements

- [ ] GDPR compliance (if EU users)
- [ ] PCI DSS (if processing payments)
- [ ] SOC 2 certification (if enterprise clients)
- [ ] HIPAA (if health data)
- [ ] Local data protection laws

---

**Last Updated:** January 2025  
**Security Level:** Enterprise Grade  
**Deployment:** Production Ready
