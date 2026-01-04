#!/bin/bash

# Deployment Script for Noteds.com
# Quick deployment script for production

set -e  # Exit on any error

echo "🚀 Starting deployment..."

# Step 1: Pull latest changes
echo "→ Pulling latest changes from git..."
git pull origin main

# Step 2: Install Composer dependencies (production only)
echo "→ Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

# Step 3: Install NPM dependencies
echo "→ Installing NPM dependencies..."
npm install

# Step 4: Build assets
echo "→ Building assets..."
npm run build

# Step 5: Ensure storage link exists (create if not exists)
echo "→ Ensuring storage symlink exists..."
php artisan storage:link --force

# Step 6: Run database migrations
echo "→ Running database migrations..."
php artisan migrate --force

# Step 7: Optimize application
echo "→ Optimizing application..."
php artisan optimize

echo ""
echo "✅ Deployment completed successfully!"
echo ""
echo "📝 Note: If using queue workers, restart them manually:"
echo "   sudo supervisorctl restart laravel-worker:*"
echo "   or"
echo "   php artisan queue:restart"
echo ""
