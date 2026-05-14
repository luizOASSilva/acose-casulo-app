#!/bin/sh

# 1. Limpa caches antigos para garantir que a APP_KEY nova seja lida
php artisan config:clear
php artisan cache:clear

# 2. Roda as migrations (Cria as tabelas que faltam, como a 'sessions')
php artisan migrate --force

# 3. O SEEDER É O PERIGO:
# Só rode o seed se o banco estiver vazio.
# Se rodar toda vez e der erro de "Duplicate entry", o 500 volta.
# php artisan db:seed --force # (Comente esta linha se o banco já tem dados)

# 4. Otimização (Opcional para deploy)
php artisan config:cache
php artisan route:cache

# 5. Inicia os serviços
php-fpm -D
nginx -g "daemon off;"
