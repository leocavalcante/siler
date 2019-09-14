FROM php:7.3
COPY --from=composer /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PHAN_ALLOW_XDEBUG=1

RUN apt-get update && apt-get upgrade -y libzip-dev unzip \
    && yes | pecl install ast mongodb zip xdebug \
    && docker-php-ext-enable ast mongodb zip xdebug

COPY . /opt/siler
WORKDIR /opt/siler