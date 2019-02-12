<?php

declare(strict_types=1);
require_once __DIR__ . '/../../vendor/autoload.php';

use Siler\Swoole;

$echo = function ($frame) {
    Swoole\broadcast($frame->data);
};

$onOpen = function () {
    echo "Connection stabilised\n";
};

$onClose = function () {
    echo "Connection closed\n";
};

Swoole\websocket(9502)($echo, $onOpen, $onClose);
