#!/bin/sh

cd /var/www

php artisan cache:clear
php artisan config:clear
php artisan storage:link

chmod 777 /var/www/bootstrap/cache
php artisan queue:work &
php artisan migrate --force

php artisan optimize
php artisan filament:optimize

/usr/bin/supervisord -c /etc/supervisord.conf
