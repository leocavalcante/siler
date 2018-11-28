<?php

declare(strict_types=1);

/**
 * Siler helpers to work with Swoole.
 */

namespace Siler\Swoole;

use Siler\Container;
use Swoole\Http\Server;

/**
 * Sets the HTTP server handle.
 *
 * @param callable $handler The handler function which receives $request and $response objects
 */
function handle(callable $handler)
{
    Container\set('swoole_handler', $handler);
}

/**
 * Starts a Swoole HTTP server.
 *
 * @param string $host The host that the server should bind
 * @param int    $port The port that the server should bind
 */
function start(string $host, int $port)
{
    $server = new Server($host, $port);
    $server->on('request', Container\get('swoole_handler'));
    $server->start();
}

/**
 * Casts a regular Swoole\Http\Request to Siler's tuple-mimic request;.
 *
 * @param mixed $request The request to be casted
 */
function cast($request)
{
    return [$request->server['request_method'], $request->server['request_uri']];
}
