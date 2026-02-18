#!/bin/sh
set -e

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Keep lifecycle scheduler running in-app.
php artisan schedule:work &

exec php -S 0.0.0.0:${PORT:-8080} -t public

