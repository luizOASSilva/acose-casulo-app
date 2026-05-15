#!/bin/sh

chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

php artisan migrate --force

php artisan config:cache  # ← descomente isso

php-fpm -D
nginx -g "daemon off;"
