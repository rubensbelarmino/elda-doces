FROM php:8.4-fpm-alpine

RUN docker-php-ext-install -j"$(nproc)" pdo_mysql opcache

COPY docker/php.ini /usr/local/etc/php/conf.d/99-doce.ini
WORKDIR /var/www/html
COPY . .
RUN chown -R www-data:www-data /var/www/html/storage

USER www-data
EXPOSE 9000
CMD ["php-fpm"]
