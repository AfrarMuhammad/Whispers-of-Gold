FROM php:8.1-apache

# Install extensions
RUN docker-php-ext-install mysqli

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html


EXPOSE 80
