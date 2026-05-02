FROM php:8.3-apache

RUN docker-php-ext-install pdo pdo_mysql
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html
COPY public /var/www/html/public
COPY src /var/www/html/src
COPY storage /var/www/html/storage

RUN a2enmod rewrite
