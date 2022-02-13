FROM php:8.0-fpm

RUN apt-get update -y && apt-get install -y zip libpng-dev libxml2-dev zlib1g-dev libgmp-dev libzip-dev

RUN docker-php-ext-install pdo_mysql soap gd gmp zip exif
RUN docker-php-ext-enable pdo_mysql soap gd gmp zip exif

RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini


COPY ./php.ini /usr/local/etc/php/php.ini
COPY ./composer.phar /usr/local/bin/composer

