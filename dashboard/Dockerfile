FROM php:8.3-apache

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html
COPY app /var/www/html/app
COPY bootstrap /var/www/html/bootstrap
COPY public /var/www/html/public
COPY routes /var/www/html/routes
COPY src /var/www/html/src
COPY storage /var/www/html/storage
COPY composer.json /var/www/html/composer.json

RUN a2enmod rewrite
