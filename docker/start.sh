#!/bin/sh

chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# ← adiciona isso
mkdir -p /var/www/html/storage/logs
ln -sf /dev/stdout /var/www/html/storage/logs/laravel.log

php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan migrate --force
php artisan config:cache

php-fpm -D
nginx -g "daemon off;"
