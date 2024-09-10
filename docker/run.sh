#!/bin/sh

cd /var/www

php artisan migrate
php artisan cache:clear
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

php artisan queue:work &

/usr/bin/supervisord -c /etc/supervisord.conf
