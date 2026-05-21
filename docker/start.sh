#!/bin/sh
set -e

echo "Preparando pastas..."

mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/bootstrap/cache

chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

ln -sf /dev/stdout /var/www/html/storage/logs/laravel.log

echo "Limpando caches Laravel..."
php artisan optimize:clear || true
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true
php artisan route:clear || true
php artisan event:clear || true

echo "Rodando migrations..."
php artisan migrate --force

echo "Cacheando config..."
php artisan config:cache

echo "Testando Nginx..."
nginx -t

echo "Iniciando scheduler..."
(while true; do php artisan schedule:run; sleep 60; done) &

echo "Iniciando PHP-FPM..."
php-fpm -D

echo "Iniciando Nginx..."
nginx -g "daemon off;"
