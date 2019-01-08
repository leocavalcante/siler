FROM php:7.2-cli

RUN pecl install swoole
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN echo "extension=swoole.so" >> "$PHP_INI_DIR/php.ini"

WORKDIR /app

CMD [ "php", "/app/examples/swoole/index.php" ]