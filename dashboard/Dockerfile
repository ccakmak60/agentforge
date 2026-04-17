FROM php:8.3-apache

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html
COPY public /var/www/html/public
COPY src /var/www/html/src
COPY storage /var/www/html/storage

RUN a2enmod rewrite
