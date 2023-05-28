# Stage 1: Build the Laravel app
FROM composer:2.1.6 as build
WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-scripts --no-autoloader

COPY . .
RUN composer dump-autoload --optimize

# Stage 2: Serve the app with PHP and Nginx
FROM php:7.4-fpm-alpine
WORKDIR /var/www/html

RUN apk add --no-cache nginx supervisor

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY --from=build /var/www/html /var/www/html

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
