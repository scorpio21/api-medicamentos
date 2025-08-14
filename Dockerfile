# syntax=docker/dockerfile:1
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json /app/composer.json
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction

FROM php:8.2-cli AS app
RUN docker-php-ext-install pdo_sqlite
WORKDIR /app
COPY --from=vendor /app/vendor /app/vendor
COPY . /app
EXPOSE 8080
ENV DB_PATH=/app/var/database.sqlite
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public", "public/index.php"]