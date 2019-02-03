FROM php:7.3-cli

RUN pecl install swoole
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
RUN echo "extension=swoole.so" >> "$PHP_INI_DIR/php.ini"

WORKDIR /app
EXPOSE 9502

CMD [ "php", "/app/examples/swoole-chat/server.php" ]