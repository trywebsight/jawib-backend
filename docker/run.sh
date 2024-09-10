#!/bin/sh

cd /var/www

php artisan cache:clear
php artisan config:clear
php artisan storage:link

chmod 777 /var/www/bootstrap/cache
php artisan queue:work &
php artisan migrate --force

/usr/bin/supervisord -c /etc/supervisord.conf
