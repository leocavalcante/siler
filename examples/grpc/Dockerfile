FROM php:7.4

RUN apt-get update && apt-get install -y zlib1g-dev \
  && pecl install swoole grpc \
  && docker-php-ext-enable swoole grpc
