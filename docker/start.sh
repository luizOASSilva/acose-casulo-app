#!/bin/sh

# 1. Garante permissões de escrita antes de tudo
# Se o Laravel não puder escrever o cache, ele dá 500
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 2. Limpa tudo (Fundamental para evitar conflitos de variáveis)
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 3. Roda as migrations (Garante que as tabelas existem)
php artisan migrate --force

# 4. CONFIGURAÇÃO CRÍTICA:
# Em vez de config:cache (que trava os valores), use apenas o clear em produção
# se você estiver tendo problemas de conexão.
# php artisan config:cache # Descomente apenas quando tudo estiver 100%

# 5. Inicia os serviços
php-fpm -D
nginx -g "daemon off;"
