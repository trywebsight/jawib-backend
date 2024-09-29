#!/bin/sh

cd /var/www

# php artisan cache:clear
# php artisan config:clear
php artisan optimize:clear
php artisan filament:optimize-clear
php artisan storage:link
php artisan package:discover --ansi
php artisan filament:upgrade
php artisan vendor:publish --tag=laravel-assets --ansi --force


chmod 777 /var/www/bootstrap/cache
php artisan queue:work &
php artisan migrate --force

php artisan optimize
php artisan filament:optimize

/usr/bin/supervisord -c /etc/supervisord.conf
