<?php

declare(strict_types=1);

/**
 * Siler module to work with Swoole.
 */

namespace Siler\Swoole;

use Siler\Container;

const SWOOLE_HTTP_REQUEST = 'swoole_http_request';
const SWOOLE_HTTP_REQUEST_ENDED = 'swoole_http_request_ended';
const SWOOLE_HTTP_RESPONSE = 'swoole_http_response';
const SWOOLE_WEBSOCKET_SERVER = 'swoole_websocket_server';

/**
 * Starts a Swoole HTTP server.
 *
 * @param string $host The host that the server should bind
 * @param int    $port The port that the server should bind
 *
 * @return \Closure
 */
function start(int $port = 80, string $host = '0.0.0.0'): \Closure
{
    $server = new \Swoole\Http\Server($host, $port);

    return function ($handler) use ($server) {
        $server->on('request', function ($request, $response) use ($handler) {
            Container\set(SWOOLE_HTTP_REQUEST_ENDED, false);
            Container\set(SWOOLE_HTTP_REQUEST, $request);
            Container\set(SWOOLE_HTTP_RESPONSE, $response);

            return $handler($request, $response);
        });

        $server->start();
    };
}

/**
 * Gets the current Swoole HTTP request.
 */
function request()
{
    return Container\get(SWOOLE_HTTP_REQUEST);
}

/**
 * Gets the current Swoole HTTP response.
 */
function response()
{
    return Container\get(SWOOLE_HTTP_RESPONSE);
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
    if (Container\get(SWOOLE_HTTP_REQUEST_ENDED) === true) {
        return null;
    }

    response()->status($status);

    foreach ($headers as $key => $value) {
        response()->header($key, $value);
    }

    Container\set(SWOOLE_HTTP_REQUEST_ENDED, true);

    return response()->end($content);
}

/**
 * Sugar to emit() JSON encoded data.
 *
 * @param mixed $data
 * @param int   $status
 * @param array $headers
 */
function json($data, int $status = 200, array $headers = [])
{
    $content = json_encode($data);

    if (false === $content) {
        $error = json_last_error_msg();
        throw new \UnexpectedValueException($error);
    }

    $headers = array_merge(['Content-Type' => 'application/json'], $headers);

    return emit($content, $status, $headers);
}

/**
 * Returns a Closure that starts a websocket server.
 *
 * @param int    $port
 * @param string $host
 *
 * @return \Closure
 */
function websocket(int $port = 9502, string $host = '0.0.0.0'): \Closure
{
    $server = new \Swoole\WebSocket\Server($host, $port);
    Container\set(SWOOLE_WEBSOCKET_SERVER, $server);

    return function (callable $handler, ?callable $onOpen = null, ?callable $onClose = null) use ($server) {
        $server->on('open', function ($server, $request) use ($onOpen) {
            if (!is_null($onOpen)) {
                $onOpen($request, $server);
            }
        });

        $server->on('message', function ($server, $frame) use ($handler) {
            return $handler($frame, $server);
        });

        $server->on('close', function ($server, $fd) use ($onClose) {
            if (!is_null($onClose)) {
                $onClose($fd, $server);
            }
        });

        return $server->start();
    };
}

/**
 * Pushes a message to a specific websocket client.
 *
 * @param string $message
 * @param int    $fd
 *
 * @return mixed
 */
function push(string $message, int $fd)
{
    if (!Container\has(SWOOLE_WEBSOCKET_SERVER)) {
        throw new \OutOfBoundsException('There is no server to push.');
    }

    $server = Container\get(SWOOLE_WEBSOCKET_SERVER);

    return $server->push($fd, $message);
}

/**
 * Broadcasts a message to every websocket client.
 *
 * @param string $message
 */
function broadcast(string $message)
{
    if (!Container\has(SWOOLE_WEBSOCKET_SERVER)) {
        throw new \OutOfBoundsException('There is no server to broadcast.');
    }

    $server = Container\get(SWOOLE_WEBSOCKET_SERVER);

    foreach ($server->connections as $fd) {
        push($message, $fd);
    }
}

/**
 * Enable CORS in a Swoole Response.
 *
 * @param array $origin
 * @param array $methods
 * @param array $headers
 */
function cors(array $origin = ['*'], array $methods = [], array $headers = [])
{
    $response = Container\get(SWOOLE_HTTP_RESPONSE);
    $response->header('Access-Control-Allow-Origin', implode(',', $origin));

    if (!empty($methods)) {
        $response->header('Access-Control-Allow-Methods', implode(',', $methods));
    }

    if (!empty($headers)) {
        $response->header('Access-Control-Allow-Headers', implode(',', $headers));
    }

    $request = Container\get(SWOOLE_HTTP_REQUEST);

    if ('OPTIONS' === $request->server['request_method']) {
        $response->end('');
    }
}
