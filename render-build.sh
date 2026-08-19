#!/usr/bin/env bash
# Exit on error
set -e

echo "--- Installing PHP Dependencies ---"
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

echo "--- Installing & Building Frontend Assets ---"
npm install
npm run build

echo "--- Clearing & Caching Configurations ---"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "--- Running Database Migrations ---"
php artisan migrate --force

echo "--- Deployment Build Finished Successfully ---"
