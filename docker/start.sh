echo "Preparando storage..."

mkdir -p /var/www/html/storage/app/public/media/articles
mkdir -p /var/www/html/storage/app/public/media/activities
mkdir -p /var/www/html/storage/app/public/media/partners
mkdir -p /var/www/html/storage/app/public/media/general
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

rm -rf /var/www/html/public/storage

ln -sfn \
    /var/www/html/storage/app/public \
    /var/www/html/public/storage

chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

chmod -R 775 \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

echo "Testando storage como www-data..."

su -s /bin/sh www-data -c \
    "touch /var/www/html/storage/app/public/media/general/.write-test"

su -s /bin/sh www-data -c \
    "rm -f /var/www/html/storage/app/public/media/general/.write-test"

echo "Link criado:"
ls -la /var/www/html/public/storage
