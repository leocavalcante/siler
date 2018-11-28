<?php

declare(strict_types=1);
require __DIR__.'/../../vendor/autoload.php';

use Siler\Functional as F;
use Siler\Route;
use Siler\Swoole;

$handler = function ($req, $res) {
    $routes = [
        Route\get('/foo', F\always('foo'), Swoole\cast($req)),
        Route\get('/bar', F\always('bar'), Swoole\cast($req)),
        Route\get('/baz', F\always('baz'), Swoole\cast($req)),
    ];

    $body = Route\match($routes) ?? 'nil';
    $res->end($body);
};

Swoole\handle($handler);
Swoole\start('0.0.0.0', 9502);
