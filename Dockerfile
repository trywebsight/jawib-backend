FROM php:8.3-fpm

# Set working directory
WORKDIR /var/www

# Add docker php ext repo
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Install php extensions
RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions mbstring  pdo_mysql zip exif pcntl gd memcached sockets


RUN apt-get update && apt-get install -y \
    libicu-dev \
    libonig-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-install mbstring
RUN docker-php-ext-enable intl mbstring
# Install dependencies
RUN apt-get update -y && apt-get -y install \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    unzip \
    git \
    curl \
    lua-zlib-dev \
    libmemcached-dev \
    nginx


# Install supervisor
RUN apt-get install -y supervisor
#RUN apt-get install  -y php8.2-intl
# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy code to /var/www
COPY --chown=www:www-data . /var/www
#RUN chown -R www-data:www-data /var/www

# add root to www group
RUN chmod -R 777 /var/www/storage
RUN chmod -R 777 /var/www/storage/logs/
RUN touch /var/www/storage/logs/laravel.log
RUN chown -R www:www-data /var/www/storage/logs/laravel.log
RUN chmod -R 777 /var/www/storage/logs/laravel.log

# Copy nginx/php/supervisor configs
RUN cp docker/supervisor.conf /etc/supervisord.conf
RUN cp docker/php.ini /usr/local/etc/php/conf.d/app.ini
RUN cp docker/nginx.conf /etc/nginx/sites-enabled/default
#RUN cp docker/.htpasswd /etc/nginx/.htpasswd
# RUN cp docker/hostss /etc/hosts

# PHP Error Log Files
RUN mkdir /var/log/php
RUN touch /var/log/php/errors.log && chmod 777 /var/log/php/errors.log
RUN mkdir /var/www/public/logs/
RUN ln -s /var/www/storage/logs/laravel.log /var/www/public/logs/laravel.log
RUN ln -s /var/log/nginx/schedule.log /var/www/public/logs/schedule.log
#RUN rm composer.lock
# Deployment steps ....i
RUN composer require filament/filament --optimize-autoloader 
RUN composer install --optimize-autoloader --no-dev

#RUN composer
RUN chmod +x /var/www/docker/run.sh

EXPOSE 80
ENTRYPOINT ["/var/www/docker/run.sh"]
