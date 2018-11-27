<?php

declare(strict_types=1);
require __DIR__.'/../../vendor/autoload.php';

use Siler\Swoole;
use Siler\Route;
use Siler\Functional as F;

Swoole\handle(function ($req, $res) {
    $body = Route\get('/', F\always('Hello World'), Swoole\request($req));
    $res->end($body);
});

Swoole\start('0.0.0.0', 9502);
