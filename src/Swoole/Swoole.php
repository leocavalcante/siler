<?php

declare(strict_types=1);

namespace Siler\Swoole;

use Siler\Container;

function handle(callable $handler)
{
    Container\set('swoole_handler', $handler);
}

function start(string $host, int $port)
{
    $server = new swoole_http_server($host, $port);
    $server->on('request', Container\get('swoole_handler'));
    $server->start();
}
