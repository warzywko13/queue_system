FROM php:8.3-fpm

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt-get update && apt-get install -y libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-enable intl \
    && docker-php-ext-install sockets \
    && docker-php-ext-enable sockets

RUN pecl install redis \
    && docker-php-ext-enable redis

WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www/html
USER www-data