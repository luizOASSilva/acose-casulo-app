#!/bin/sh

chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan migrate --force
php artisan config:cache

# ← adiciona isso para ver logs do Laravel no Render
ln -sf /dev/stdout /var/www/html/storage/logs/laravel.log

php-fpm -D
nginx -g "daemon off;"
