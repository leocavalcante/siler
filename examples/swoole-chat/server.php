<?php declare(strict_types=1);
require_once __DIR__ . '/../../vendor/autoload.php';

use Siler\Swoole;
use function Siler\Functional\puts;

$echo = function ($frame) {
    Swoole\broadcast($frame->data);
};

Swoole\websocket_hooks([
    'open' => puts("New connection\n"),
    'close' => puts("Someone leaves\n")
]);

Swoole\websocket($echo)->start();
