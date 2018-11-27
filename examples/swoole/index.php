<?php

declare(strict_types=1);
require __DIR__.'/../../vendor/autoload.php';

use Siler\Functional as F;
use Siler\Route;
use Siler\Swoole;

Swoole\handle(function ($req, $res) {
    $body = Route\get('/', F\always('Hello World'), Swoole\request($req));
    $res->end($body);
});

Swoole\start('0.0.0.0', 9502);
