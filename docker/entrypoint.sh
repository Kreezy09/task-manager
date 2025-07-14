#!/bin/sh
set -e

# Run migrations and cache config
php artisan config:cache
php artisan route:cache

# Start supervisord
exec /usr/bin/supervisord -c /etc/supervisord.conf 