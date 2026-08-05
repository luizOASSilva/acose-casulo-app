#!/bin/sh

set -eu

APP_DIR="/var/www/html"
PORT="${PORT:-10000}"

case "$PORT" in
    ''|*[!0-9]*)
        echo "ERRO: PORT precisa ser um número."
        exit 1
        ;;
esac

echo "======================================"
echo "Iniciando Laravel no Render"
echo "APP_ENV=${APP_ENV:-production}"
echo "PORT=${PORT}"
echo "RUN_MIGRATIONS=${RUN_MIGRATIONS:-true}"
echo "RUN_SEEDERS=${RUN_SEEDERS:-false}"
echo "RUN_SCHEDULER=${RUN_SCHEDULER:-false}"
echo "======================================"

cd "$APP_DIR"

echo "Configurando porta do Nginx..."

sed "s/__PORT__/${PORT}/g" \
    /etc/nginx/nginx.conf.template \
    > /etc/nginx/nginx.conf

echo "Preparando diretórios do Laravel..."

mkdir -p \
    storage/logs \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache

echo "Criando link público do storage..."

rm -rf public/storage
ln -sfn "$APP_DIR/storage/app/public" "$APP_DIR/public/storage"

echo "Ajustando permissões..."

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "Enviando log do Laravel para o stdout..."

touch storage/logs/laravel.log
chown www-data:www-data storage/logs/laravel.log
ln -sfn /dev/stdout storage/logs/laravel.log

echo "Testando escrita no storage..."

if su -s /bin/sh www-data -c \
    "touch storage/app/public/.write-test && rm -f storage/app/public/.write-test"
then
    echo "Storage gravável."
else
    echo "ERRO: usuário www-data não consegue gravar no storage."
    ls -la storage storage/app storage/app/public || true
    exit 1
fi

echo "Limpando caches antigos..."

php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan event:clear || true
php artisan clear-compiled || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "Executando migrations..."

    ATTEMPT=1
    MAX_ATTEMPTS=12

    until php artisan migrate --force --no-interaction; do
        if [ "$ATTEMPT" -ge "$MAX_ATTEMPTS" ]; then
            echo "ERRO: migrations falharam após ${MAX_ATTEMPTS} tentativas."
            exit 1
        fi

        echo "Banco indisponível ou migration falhou."
        echo "Nova tentativa em 5 segundos (${ATTEMPT}/${MAX_ATTEMPTS})..."

        ATTEMPT=$((ATTEMPT + 1))
        sleep 5
    done

    echo "Migrations concluídas."
else
    echo "Migrations desativadas."
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    if [ -n "${SEEDER_CLASS:-}" ]; then
        echo "Executando seeder: ${SEEDER_CLASS}"

        php artisan db:seed \
            --class="${SEEDER_CLASS}" \
            --force \
            --no-interaction
    else
        echo "Executando DatabaseSeeder..."

        php artisan db:seed \
            --force \
            --no-interaction
    fi
else
    echo "Seeders desativados."
fi

echo "Limpando caches após migrations..."

php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan event:clear || true
php artisan clear-compiled || true

echo "Gerando cache de configuração..."

php artisan config:cache

echo "Validando configuração do Nginx..."

nginx -t

if [ "${RUN_SCHEDULER:-false}" = "true" ]; then
    echo "Iniciando Laravel Scheduler..."

    (
        while true; do
            php artisan schedule:run --quiet || true
            sleep 60
        done
    ) &
else
    echo "Scheduler desativado."
fi

echo "Iniciando PHP-FPM..."

php-fpm -D

echo "Iniciando Nginx na porta ${PORT}..."

exec nginx -g "daemon off;"
