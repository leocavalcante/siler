<?php

declare(strict_types=1);

namespace Siler\Swoole;

use Siler\Container;
use Swoole\Http\Server;

function handle(callable $handler)
{
    Container\set('swoole_handler', $handler);
}

function start(string $host, int $port)
{
    $server = new Server($host, $port);
    $server->on('request', Container\get('swoole_handler'));
    $server->start();
}

function request($request)
{
    return [$request->server['request_method'], $request->server['request_uri']];
}
