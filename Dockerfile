FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    nginx \
    curl \
    zip \
    unzip \
    git \
    oniguruma-dev \
    libxml2-dev \
    mysql-client \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    xml \
    bcmath \
    pcntl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/start.sh /start.sh
COPY docker/ca.pem /etc/ssl/certs/aiven-ca.pem
RUN chmod +x /start.sh

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8080

CMD ["/start.sh"]
