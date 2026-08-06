FROM php:8.4-fpm-alpine

ENV APP_ENV=production \
    COMPOSER_ALLOW_SUPERUSER=1

RUN apk add --no-cache \
        nginx \
        ca-certificates \
        curl \
        git \
        unzip \
        shadow \
        libzip \
        oniguruma \
        libxml2 \
        icu-libs \
        freetype \
        libjpeg-turbo \
        libpng \
        mysql-client \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        curl-dev \
        libzip-dev \
        oniguruma-dev \
        libxml2-dev \
        icu-dev \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        xml \
        bcmath \
        pcntl \
        curl \
        zip \
        opcache \
        intl \
        gd \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/*

# Os Secret Files do Render são legíveis pelo grupo de GID 1000.
# Cria o grupo caso ele ainda não exista e adiciona www-data a ele.
RUN if ! awk -F: '$3 == 1000 { found = 1 } END { exit(found ? 0 : 1) }' /etc/group; then \
        groupadd --gid 1000 render-secrets; \
    fi \
    && RENDER_SECRETS_GROUP="$(awk -F: '$3 == 1000 { print $1; exit }' /etc/group)" \
    && usermod -a -G "${RENDER_SECRETS_GROUP}" www-data \
    && id www-data

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN { \
        echo 'upload_max_filesize=50M'; \
        echo 'post_max_size=50M'; \
        echo 'memory_limit=256M'; \
        echo 'max_execution_time=300'; \
        echo 'max_input_time=300'; \
        echo 'expose_php=Off'; \
        echo 'display_errors=Off'; \
        echo 'log_errors=On'; \
        echo 'error_log=/proc/self/fd/2'; \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=0'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.save_comments=1'; \
    } > /usr/local/etc/php/conf.d/production.ini

# Mantém as variáveis do Render disponíveis para os workers do PHP-FPM.
RUN { \
        echo '[www]'; \
        echo 'clear_env = no'; \
        echo 'catch_workers_output = yes'; \
        echo 'pm = dynamic'; \
        echo 'pm.max_children = 5'; \
        echo 'pm.start_servers = 2'; \
        echo 'pm.min_spare_servers = 1'; \
        echo 'pm.max_spare_servers = 3'; \
    } > /usr/local/etc/php-fpm.d/zz-render.conf

WORKDIR /var/www/html

# Instala as dependências antes de copiar o projeto inteiro,
# permitindo melhor aproveitamento do cache do Docker.
COPY composer.json composer.lock ./

RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --prefer-dist \
        --optimize-autoloader \
        --no-scripts \
    && composer clear-cache

COPY . .

RUN composer dump-autoload \
        --no-dev \
        --classmap-authoritative \
        --no-interaction \
    && mkdir -p \
        storage/logs \
        storage/app/public \
        storage/app/public/media/articles \
        storage/app/public/media/activities \
        storage/app/public/media/partners \
        storage/app/public/media/general \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        bootstrap/cache \
    && chown -R www-data:www-data \
        storage \
        bootstrap/cache \
    && chmod -R 775 \
        storage \
        bootstrap/cache

COPY docker/nginx.conf /etc/nginx/nginx.conf.template
COPY docker/start.sh /start.sh
COPY docker/ca.pem /etc/ssl/certs/aiven-ca.pem

RUN chmod +x /start.sh \
    && chmod 644 /etc/ssl/certs/aiven-ca.pem

EXPOSE 10000

CMD ["/start.sh"]
