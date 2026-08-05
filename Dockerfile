FROM php:8.4-fpm-alpine

ENV APP_ENV=production \
    COMPOSER_ALLOW_SUPERUSER=1

RUN apk add --no-cache \
        nginx \
        ca-certificates \
        curl \
        git \
        unzip \
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

# Mantém as variáveis do Render disponíveis para o PHP-FPM.
RUN { \
        echo '[www]'; \
        echo 'clear_env = no'; \
        echo 'catch_workers_output = yes'; \
        echo 'pm.max_children = 5'; \
        echo 'pm.start_servers = 2'; \
        echo 'pm.min_spare_servers = 1'; \
        echo 'pm.max_spare_servers = 3'; \
    } > /usr/local/etc/php-fpm.d/zz-render.conf

WORKDIR /var/www/html

# Instala dependências antes de copiar o projeto inteiro para aproveitar cache.
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
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/nginx.conf /etc/nginx/nginx.conf.template
COPY docker/start.sh /start.sh
COPY docker/ca.pem /etc/ssl/certs/aiven-ca.pem

RUN chmod +x /start.sh \
    && chmod 644 /etc/ssl/certs/aiven-ca.pem

EXPOSE 10000

CMD ["/start.sh"]
