FROM php:8.4-fpm-alpine3.20

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock* ./

RUN composer install --no-interaction --no-scripts

COPY . .

CMD ["php-fpm"]

