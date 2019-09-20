ARG version=7.3
FROM php:$version
COPY --from=composer /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PHAN_ALLOW_XDEBUG=1

RUN apt-get update && apt-get upgrade -y libzip-dev unzip \
    && yes | pecl install -f ast mongodb zip xdebug-2.8.0 \
    && docker-php-ext-enable ast mongodb zip xdebug

COPY . /opt/siler
WORKDIR /opt/siler