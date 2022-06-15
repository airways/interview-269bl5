FROM php:8.1-cli-bullseye

RUN apt-get update && apt-get upgrade -y && apt-get install -y libicu-dev libonig-dev
RUN docker-php-ext-install mbstring intl mysqli pdo_mysql
RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY ./tweaks.ini /usr/local/etc/php/conf.d/tweaks.ini

WORKDIR /app
STOPSIGNAL SIGINT
CMD ["php", "/app/scripts/start.php"]
