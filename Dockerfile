ARG version=7.4
FROM php:$version
COPY --from=composer /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update && apt-get upgrade -y libzip-dev unzip \
    && yes | pecl install -f zip xdebug \
    && docker-php-ext-enable zip xdebug

COPY . /opt/siler
WORKDIR /opt/siler