#!/usr/bin/env bash
set -e

# Render exposes the public URL in RENDER_EXTERNAL_URL.
# If APP_URL is not explicitly set, we use it automatically.
if [ -n "${RENDER_EXTERNAL_URL:-}" ] && [ -z "${APP_URL:-}" ]; then
  export APP_URL="${RENDER_EXTERNAL_URL}"
fi

if [ -n "${APP_URL:-}" ] && [ -z "${ASSET_URL:-}" ]; then
  export ASSET_URL="${APP_URL}"
fi

echo "Installing PHP dependencies..."
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --working-dir=/var/www/html

echo "Caching configuration..."
php artisan config:cache

echo "Caching views..."
php artisan view:cache

echo "Ensuring storage symlink..."
php artisan storage:link || true

echo "Running database migrations..."
php artisan migrate --force

if [ "${RUN_DB_SEED:-false}" = "true" ]; then
  echo "Running seeders..."
  php artisan db:seed --force
fi
