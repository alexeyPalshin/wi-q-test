FROM php:8.4-fpm-alpine3.20

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-interaction --no-scripts --no-dev

USER 1000
VOLUME /app

CMD ["php-fpm"]

