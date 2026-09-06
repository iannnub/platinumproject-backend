#!/bin/bash
# ==============================================================================
# Platinum Project Backend Production Deploy Script
# Run this script on server (cPanel Terminal / SSH) after git pull
# ==============================================================================

set -e

echo "🚀 Starting Platinum Project Backend Deployment..."

# 1. Maintenance Mode
php artisan down || true

# 2. Update dependencies
composer install --no-dev --optimize-autoloader --no-interaction

# 3. Migrate Database
php artisan migrate --force

# 4. Clear and Cache Configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Bring application up
php artisan up

echo "✅ Backend successfully deployed and optimized!"
