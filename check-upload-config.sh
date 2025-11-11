#!/bin/bash

# Script untuk verifikasi konfigurasi upload file
# Usage: bash check-upload-config.sh

echo "=========================================="
echo "Upload Configuration Verification"
echo "=========================================="
echo ""

# Check PHP Configuration
echo "1. PHP Configuration:"
echo "   Checking PHP-FPM settings..."
php -i | grep -E "upload_max_filesize|post_max_size|memory_limit|max_execution_time" | head -4
echo ""

# Check Nginx Configuration
echo "2. Nginx Configuration:"
echo "   Checking client_max_body_size..."
if command -v nginx &> /dev/null; then
    nginx -T 2>/dev/null | grep -i "client_max_body_size" | head -5
else
    echo "   Nginx not found in PATH. Please check manually in /etc/nginx/sites-available/noteds"
fi
echo ""

# Check PHP-FPM Pool Configuration
echo "3. PHP-FPM Pool Configuration:"
if [ -f "/etc/php/8.3/fpm/pool.d/www.conf" ]; then
    echo "   PHP 8.3 FPM pool settings:"
    grep -E "upload_max_filesize|post_max_size|memory_limit|max_execution_time" /etc/php/8.3/fpm/pool.d/www.conf | head -4
elif [ -f "/etc/php/8.2/fpm/pool.d/www.conf" ]; then
    echo "   PHP 8.2 FPM pool settings:"
    grep -E "upload_max_filesize|post_max_size|memory_limit|max_execution_time" /etc/php/8.2/fpm/pool.d/www.conf | head -4
else
    echo "   PHP-FPM pool config not found. Please check manually."
fi
echo ""

# Check Laravel .env
echo "4. Laravel .env Configuration:"
if [ -f ".env" ]; then
    echo "   Checking for upload-related settings..."
    grep -E "APP_ENV|APP_DEBUG" .env | head -2
else
    echo "   .env file not found in current directory."
fi
echo ""

echo "=========================================="
echo "Verification completed!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. If PHP settings are not correct, update them in aaPanel or php.ini"
echo "2. If Nginx client_max_body_size is not 200M, update /etc/nginx/sites-available/noteds"
echo "3. Restart services:"
echo "   sudo systemctl reload nginx"
echo "   sudo systemctl restart php8.3-fpm  # or php8.2-fpm"
echo "4. Clear Laravel cache:"
echo "   php artisan config:clear"
echo "   php artisan cache:clear"

