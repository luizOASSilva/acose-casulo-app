#!/bin/sh
set -e

echo "Preparando pastas..."

mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/views

chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

ln -sf /dev/stdout /var/www/html/storage/logs/laravel.log

echo "Limpando caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "Rodando migrations..."
php artisan migrate --force

echo "Cacheando config..."
php artisan config:cache

echo "Testando nginx..."
nginx -t

echo "Iniciando scheduler..."
(while true; do php artisan schedule:run; sleep 60; done) &

echo "Iniciando php-fpm..."
php-fpm -D

echo "Iniciando nginx..."
nginx -g "daemon off;"
