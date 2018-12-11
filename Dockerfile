ARG php_version=7.3
FROM php:${php_version}

RUN apt-get update \
  && apt-get install -y wget unzip libicu-dev \
  && docker-php-ext-configure intl \
  && docker-php-ext-install -j$(nproc) intl \
  && pecl install xdebug-2.7.0beta1 \
  && docker-php-ext-enable xdebug

WORKDIR /app

COPY install_composer.sh /app
COPY composer.json /app
COPY composer.lock /app
RUN ./install_composer.sh \
  && php composer.phar install

COPY . /app
RUN php composer.phar test
