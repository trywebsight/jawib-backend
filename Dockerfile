FROM php:8.2-fpm-buster

# Set working directory
WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    libpng-dev \
    zip \
    curl \
    lua-zlib-dev \
    nginx \
    supervisor python3-pip python3-cffi python3-brotli libpango-1.0-0 libharfbuzz0b libpangoft2-1.0-0 libzip-dev sudo   &&  apt-get clean && rm -rf /var/lib/apt/lists/*


RUN apt-get update && apt-get install -y \
    libicu-dev \
    libonig-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-install mbstring
RUN docker-php-ext-enable intl mbstring


RUN docker-php-ext-install  pdo pdo_mysql zip exif gd


# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


# Install supervisor
RUN apt-get install -y supervisor


RUN adduser www-data sudo
RUN echo '%sudo ALL=(ALL) NOPASSWD:/usr/sbin/nginx' >> /etc/sudoers
RUN touch /var/log/supervisord.log && touch /var/log/nginx/php-error.log 
RUN touch /var/log/nginx/php-access.log && touch /var/log/nginx/error.log 
RUN touch /var/log/nginx/access.log && chown www-data: -R /var/log/nginx/ 
RUN chown www-data /var/log/supervisord.log  && chown www-data: /usr/local/sbin/php-fpm 
RUN chown www-data: -R /usr/local/etc && touch /var/run/supervisord.pid && chown www-data: /var/run/supervisord.pid


# Copy code to /var/www
COPY  . /var/www
RUN chown -R www-data:www-data /var/www

RUN chmod -R 775 /var/www/storage
RUN chmod -R   777  /var/www/storage/logs/


# Copy nginx/php/supervisor configs
RUN cp docker/supervisor.conf /etc/supervisord.conf
RUN cp docker/php.ini /usr/local/etc/php/conf.d/app.ini
RUN cp docker/nginx.conf /etc/nginx/sites-enabled/default


# Deployment steps ....
RUN rm composer.lock
USER www-data

RUN composer install 

#RUN composer fund
RUN chmod +x /var/www/docker/run.sh



EXPOSE 80
ENTRYPOINT ["/var/www/docker/run.sh"]
