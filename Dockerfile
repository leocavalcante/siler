ARG php_version=7.2
FROM php:${php_version}

RUN apt-get update \
  && apt-get install -y wget zlib1g-dev libicu-dev \
  && docker-php-ext-configure intl \
  && docker-php-ext-install -j$(nproc) zip intl \
  && pecl install xdebug \
  && docker-php-ext-enable xdebug

WORKDIR /app

COPY install_composer.sh /app
COPY composer.json /app
COPY composer.lock /app
RUN ./install_composer.sh \
  && php composer.phar install

COPY . /app
RUN php composer.phar test
