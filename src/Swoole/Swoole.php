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
    $server->on('request', function ($request, $response) {
        Container\set('swoole_request_ended', false);
        Container\set('swoole_request', $request);
        Container\set('swoole_response', $response);

        $handler = Container\get('swoole_handler');
        return $handler($request, $response);
    });
    $server->start();
}

/**
 * Casts a regular Swoole\Http\Request to Siler's tuple-mimic request;.
 *
 * @param mixed $request The request to be casted
 *
 * @return array
 */
function cast($request)
{
    return [$request->server['request_method'], $request->server['request_uri']];
}

/**
 * Gets the current Swoole HTTP request.
 */
function request()
{
    return Container\get('swoole_request');
}

/**
 * Gets the current Swoole HTTP response.
 */
function response()
{
    return Container\get('swoole_response');
}

/**
 * Controls Swoole halting avoiding calling end() more than once.
 *
 * @param string $content Content for the output.
 * @param int    $status  HTTP response status code.
 * @param array  $headers HTTP response headers.
 */
function emit(string $content, int $status = 200, array $headers = [])
{
    if (Container\get('swoole_request_ended') === true) {
        return null;
    }

    response()->status($status);

    foreach ($headers as $key => $value) {
        response()->header($key, $value);
    }

    Container\set('swoole_request_ended', true);

    return response()->end($content);
}
