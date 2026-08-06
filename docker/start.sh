#!/bin/sh

set -eu

APP_ENV="${APP_ENV:-production}"
FILESYSTEM_DISK="${FILESYSTEM_DISK:-public}"
RUN_MIGRATIONS="${RUN_MIGRATIONS:-true}"
RUN_SEEDERS="${RUN_SEEDERS:-false}"
RUN_FRESH_MIGRATIONS="${RUN_FRESH_MIGRATIONS:-false}"
RUN_SCHEDULER="${RUN_SCHEDULER:-true}"
SEEDER_CLASS="${SEEDER_CLASS:-}"
PORT="${PORT:-10000}"

echo "======================================"
echo "INICIANDO CONTAINER LARAVEL"
echo "APP_ENV=${APP_ENV}"
echo "APP_URL=${APP_URL:-}"
echo "FILESYSTEM_DISK=${FILESYSTEM_DISK}"
echo "RUN_MIGRATIONS=${RUN_MIGRATIONS}"
echo "RUN_FRESH_MIGRATIONS=${RUN_FRESH_MIGRATIONS}"
echo "RUN_SEEDERS=${RUN_SEEDERS}"
echo "SEEDER_CLASS=${SEEDER_CLASS}"
echo "RUN_SCHEDULER=${RUN_SCHEDULER}"
echo "PORT=${PORT}"
echo "GOOGLE_ANALYTICS_AUTH_MODE=${GOOGLE_ANALYTICS_AUTH_MODE:-}"
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

echo "Ajustando permissões do Laravel..."

chown -R www-data:www-data \
    storage \
    bootstrap/cache

chmod -R 775 \
    storage \
    bootstrap/cache

echo "Enviando logs do Laravel para stderr..."

rm -f storage/logs/laravel.log

ln -s \
    /dev/stderr \
    storage/logs/laravel.log

echo "Testando gravação no storage como www-data..."

if su -s /bin/sh www-data -c \
    "touch /var/www/html/storage/app/public/media/general/.write-test && rm -f /var/www/html/storage/app/public/media/general/.write-test"
then
    echo "Storage gravável."
else
    echo "ERRO: www-data não consegue gravar no storage."

    echo "Permissões do storage:"
    ls -la storage || true
    ls -la storage/app || true
    ls -la storage/app/public || true
    ls -la storage/app/public/media/general || true

    echo "Usuário www-data:"
    id www-data || true

    exit 1
fi

echo "Verificando link público..."

ls -la public/storage || true
readlink -f public/storage || true

echo "Verificando grupos do usuário www-data..."

id www-data

GOOGLE_ANALYTICS_AUTH_MODE_NORMALIZED="$(
    printf '%s' "${GOOGLE_ANALYTICS_AUTH_MODE:-}" |
        tr '[:upper:]' '[:lower:]'
)"

if [ "${GOOGLE_ANALYTICS_AUTH_MODE_NORMALIZED}" = "service_account" ]; then
    GOOGLE_ANALYTICS_SECRET_PATH="${GOOGLE_ANALYTICS_CREDENTIALS_PATH:-/etc/secrets/google-analytics-service-account.json}"

    echo "Verificando Secret File do Google Analytics..."
    echo "Caminho configurado: ${GOOGLE_ANALYTICS_SECRET_PATH}"

    if [ ! -f "${GOOGLE_ANALYTICS_SECRET_PATH}" ]; then
        echo "ERRO: o Secret File do Google Analytics não foi encontrado."
        echo "Esperado em: ${GOOGLE_ANALYTICS_SECRET_PATH}"
        echo "Arquivos disponíveis em /etc/secrets:"

        ls -la /etc/secrets 2>/dev/null || true

        exit 1
    fi

    if ! su -s /bin/sh www-data -c \
        "test -r \"${GOOGLE_ANALYTICS_SECRET_PATH}\""
    then
        echo "ERRO: o usuário www-data não consegue ler o Secret File."
        echo "Arquivo: ${GOOGLE_ANALYTICS_SECRET_PATH}"
        echo "Permissões encontradas:"

        ls -la "${GOOGLE_ANALYTICS_SECRET_PATH}" || true

        echo "Grupos do www-data:"
        id www-data || true

        exit 1
    fi

    echo "Secret File do Google Analytics encontrado e legível."
else
    echo "Validação de Service Account ignorada."
fi

echo "Preparando configuração do Nginx..."

if [ -f /etc/nginx/nginx.conf.template ]; then
    sed \
        -e "s/\${PORT}/${PORT}/g" \
        -e "s/listen 8080;/listen ${PORT};/g" \
        -e "s/listen 10000;/listen ${PORT};/g" \
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

    while [ "${attempt}" -le "${max_attempts}" ]; do
        echo "Executando migrations — tentativa ${attempt}/${max_attempts}..."

        if php artisan migrate --force -v; then
            echo "Migrations concluídas."
            return 0
        fi

        if [ "${attempt}" -eq "${max_attempts}" ]; then
            echo "ERRO: migrations falharam após ${max_attempts} tentativas."
            return 1
        fi

        attempt=$((attempt + 1))

        echo "Banco indisponível ou alguma migration falhou."
        echo "Nova tentativa em 5 segundos..."

        sleep 5
    done
}

run_fresh_migrations()
{
    attempt=1
    max_attempts=12

    while [ "${attempt}" -le "${max_attempts}" ]; do
        echo "Executando migrate:fresh — tentativa ${attempt}/${max_attempts}..."

        /*
         * Não existe --seed aqui.
         *
         * Os seeders são executados separadamente somente quando:
         *
         * RUN_SEEDERS=true
         */

        if php artisan migrate:fresh --force -v; then
            echo "Banco apagado e recriado pelas migrations."
            return 0
        fi

        if [ "${attempt}" -eq "${max_attempts}" ]; then
            echo "ERRO: migrate:fresh falhou após ${max_attempts} tentativas."
            return 1
        fi

        attempt=$((attempt + 1))

        echo "Banco indisponível ou alguma migration falhou."
        echo "Nova tentativa em 5 segundos..."

        sleep 5
    done
}

run_seeders()
{
    if [ "${RUN_SEEDERS}" != "true" ]; then
        echo "Seeders ignorados."
        return 0
    fi

    echo "Executando seeders..."

    if [ -n "${SEEDER_CLASS}" ]; then
        echo "Executando seeder específico:"
        echo "${SEEDER_CLASS}"

        php artisan db:seed \
            --class="${SEEDER_CLASS}" \
            --force \
            -v
    else
        echo "Nenhum SEEDER_CLASS informado."
        echo "Executando DatabaseSeeder..."

        php artisan db:seed \
            --force \
            -v
    fi

    echo "Seeders concluídos."
}

if [ "${RUN_FRESH_MIGRATIONS}" = "true" ]; then
    echo "======================================"
    echo "ATENÇÃO: RUN_FRESH_MIGRATIONS=true"
    echo "TODAS AS TABELAS DO BANCO SERÃO APAGADAS."
    echo "DATABASE=${DB_DATABASE:-}"
    echo "HOST=${DB_HOST:-}"
    echo "======================================"

    run_fresh_migrations
elif [ "${RUN_MIGRATIONS}" = "true" ]; then
    run_migrations
else
    echo "Migrations ignoradas."
fi

/*
 * O seeder é executado independentemente de ter sido usado
 * migrate ou migrate:fresh.
 *
 * Assim, é possível usar:
 *
 * RUN_FRESH_MIGRATIONS=true
 * RUN_SEEDERS=true
 * SEEDER_CLASS=Database\Seeders\DocumentCategorySeeder
 */

run_seeders

echo "Limpando caches após migrations e seeders..."

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear || true
php artisan clear-compiled || true

echo "Restaurando link público do storage..."

rm -rf public/storage

ln -sfn \
    /var/www/html/storage/app/public \
    /var/www/html/public/storage

echo "Restaurando permissões finais..."

chown -R www-data:www-data \
    storage \
    bootstrap/cache

chmod -R 775 \
    storage \
    bootstrap/cache

echo "Testando gravação final no storage..."

if su -s /bin/sh www-data -c \
    "touch /var/www/html/storage/app/public/media/general/.write-test-final && rm -f /var/www/html/storage/app/public/media/general/.write-test-final"
then
    echo "Storage final gravável."
else
    echo "ERRO: storage final não está gravável."

    ls -la storage/app/public/media/general || true
    id www-data || true

    exit 1
fi

echo "Gerando cache de configuração..."

php artisan config:cache

echo "Validando PHP-FPM..."

php-fpm -t

echo "Validando Nginx..."

nginx -t

if [ "${RUN_SCHEDULER}" = "true" ]; then
    echo "Iniciando scheduler..."

    (
        while true
        do
            php artisan schedule:run \
                --no-interaction || true

            sleep 60
        done
    ) &
else
    echo "Scheduler ignorado."
fi

echo "Iniciando PHP-FPM..."

php-fpm -D

echo "Verificando processo do PHP-FPM..."

sleep 1

if ! pgrep php-fpm > /dev/null; then
    echo "ERRO: PHP-FPM não permaneceu em execução."
    exit 1
fi

echo "Iniciando Nginx na porta ${PORT}..."
echo "Container iniciado."

exec nginx -g "daemon off;"
