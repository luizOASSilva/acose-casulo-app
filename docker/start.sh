#!/bin/sh

set -eu

echo "======================================"
echo "INICIANDO CONTAINER LARAVEL"
echo "APP_ENV=${APP_ENV:-production}"
echo "APP_URL=${APP_URL:-}"
echo "FILESYSTEM_DISK=${FILESYSTEM_DISK:-public}"
echo "RUN_MIGRATIONS=${RUN_MIGRATIONS:-true}"
echo "RUN_SEEDERS=${RUN_SEEDERS:-false}"
echo "RUN_FRESH_MIGRATIONS=${RUN_FRESH_MIGRATIONS:-false}"
echo "PORT=${PORT:-10000}"
echo "======================================"

cd /var/www/html

echo "Preparando diretórios do Laravel..."

mkdir -p storage/logs
mkdir -p storage/app/public
mkdir -p storage/app/public/media/articles
mkdir -p storage/app/public/media/activities
mkdir -p storage/app/public/media/partners
mkdir -p storage/app/public/media/general
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache
mkdir -p public

echo "Criando link público do storage..."

rm -rf public/storage
ln -sfn \
    /var/www/html/storage/app/public \
    /var/www/html/public/storage

echo "Ajustando permissões..."

chown -R www-data:www-data \
    storage \
    bootstrap/cache

chmod -R 775 \
    storage \
    bootstrap/cache

echo "Enviando logs do Laravel para stderr..."

rm -f storage/logs/laravel.log
ln -s /dev/stderr storage/logs/laravel.log

echo "Testando gravação como www-data..."

su -s /bin/sh www-data -c \
    "touch /var/www/html/storage/app/public/media/general/.write-test"

su -s /bin/sh www-data -c \
    "rm -f /var/www/html/storage/app/public/media/general/.write-test"

echo "Storage gravável."

echo "Verificando link público..."

ls -la public/storage
readlink -f public/storage || true

echo "Preparando configuração do Nginx..."

PORT="${PORT:-10000}"

if [ -f /etc/nginx/nginx.conf.template ]; then
    sed \
        -e "s/\${PORT}/${PORT}/g" \
        -e "s/listen 8080;/listen ${PORT};/g" \
        /etc/nginx/nginx.conf.template \
        > /etc/nginx/nginx.conf
fi

echo "Limpando caches antigos..."

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear || true
php artisan clear-compiled || true

run_migrations()
{
    attempt=1
    max_attempts=12

    while [ "$attempt" -le "$max_attempts" ]; do
        echo "Executando migrations — tentativa ${attempt}/${max_attempts}..."

        if php artisan migrate --force -v; then
            echo "Migrations concluídas."
            return 0
        fi

        if [ "$attempt" -eq "$max_attempts" ]; then
            echo "ERRO: migrations falharam após ${max_attempts} tentativas."
            return 1
        fi

        attempt=$((attempt + 1))

        echo "Banco indisponível ou migration falhou."
        echo "Nova tentativa em 5 segundos..."

        sleep 5
    done
}

if [ "${RUN_FRESH_MIGRATIONS:-false}" = "true" ]; then
    echo "ATENÇÃO: RUN_FRESH_MIGRATIONS=true."
    echo "O banco será apagado e recriado."

    php artisan migrate:fresh --seed --force -v
else
    if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
        run_migrations
    else
        echo "Migrations ignoradas."
    fi

    if [ "${RUN_SEEDERS:-false}" = "true" ]; then
        if [ -n "${SEEDER_CLASS:-}" ]; then
            echo "Executando seeder: ${SEEDER_CLASS}"

            php artisan db:seed \
                --class="${SEEDER_CLASS}" \
                --force \
                -v
        else
            echo "Executando DatabaseSeeder..."

            php artisan db:seed --force -v
        fi
    else
        echo "Seeders ignorados."
    fi
fi

echo "Limpando caches após migrations..."

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear || true

echo "Gerando cache de configuração..."

php artisan config:cache

echo "Restaurando permissões finais..."

chown -R www-data:www-data \
    storage \
    bootstrap/cache

chmod -R 775 \
    storage \
    bootstrap/cache

echo "Validando PHP-FPM..."

php-fpm -t

echo "Validando Nginx..."

nginx -t

echo "Iniciando scheduler..."

(
    while true; do
        php artisan schedule:run --no-interaction || true
        sleep 60
    done
) &

echo "Iniciando PHP-FPM..."

php-fpm -D

echo "Verificando processo do PHP-FPM..."

sleep 1
pgrep php-fpm > /dev/null

echo "Iniciando Nginx na porta ${PORT}..."
echo "Container iniciado."

exec nginx -g "daemon off;"
