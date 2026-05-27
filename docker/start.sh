#!/bin/sh
set -e

echo "Preparando pastas..."

mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/app/public
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/bootstrap/cache

chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

echo "Criando link público do storage..."
rm -rf /var/www/html/public/storage
ln -sfn /var/www/html/storage/app/public /var/www/html/public/storage

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

if [ "$RUN_SEEDERS" = "true" ]; then
    echo "Rodando seeders..."

    if [ -n "$SEEDER_CLASS" ]; then
        echo "Rodando seeder específico: $SEEDER_CLASS"
        php artisan db:seed --class="$SEEDER_CLASS" --force
    else
        echo "Rodando DatabaseSeeder..."
        php artisan db:seed --force
    fi
else
    echo "Seeders ignorados. Para rodar, defina RUN_SEEDERS=true."
fi

echo "Limpando caches após migrations/seeders..."
php artisan optimize:clear || true
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true
php artisan route:clear || true
php artisan event:clear || true

echo "Cacheando config..."
php artisan config:cache

echo "Testando Nginx..."
nginx -t

echo "Iniciando scheduler..."
(while true; do php artisan schedule:run --quiet; sleep 60; done) &

echo "Iniciando PHP-FPM..."
php-fpm -D

echo "Iniciando Nginx..."
nginx -g "daemon off;"
