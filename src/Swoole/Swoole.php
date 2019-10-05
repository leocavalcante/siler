<?php

declare(strict_types=1);

/*
 * Siler module to work with Swoole.
 */

namespace Siler\Swoole;

use OutOfBoundsException;
use Siler\Container;
use Siler\Encoder\Json;
use Siler\GraphQL\SubscriptionsManager;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Swoole\Table;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebsocketServer;
use UnexpectedValueException;

use const Siler\GraphQL\GQL_DATA;
use const Siler\GraphQL\WEBSOCKET_SUB_PROTOCOL;
use const Siler\Route\DID_MATCH;

const SWOOLE_HTTP_REQUEST = 'swoole_http_request';
const SWOOLE_HTTP_REQUEST_ENDED = 'swoole_http_request_ended';
const SWOOLE_HTTP_RESPONSE = 'swoole_http_response';
const SWOOLE_WEBSOCKET_SERVER = 'swoole_websocket_server';
const SWOOLE_WEBSOCKET_ONOPEN = 'swoole_websocket_onopen';
const SWOOLE_WEBSOCKET_ONCLOSE = 'swoole_websocket_onclose';

/**
 * Returns a Swoole HTTP server.
 *
 * @param callable $handler The callable to call on each request.
 * @param int $port The port binding (defaults to 9501).
 * @param string $host The host binding (defaults to 0.0.0.0).
 *
 * @return Server
 */
function http(callable $handler, int $port = 9501, string $host = '0.0.0.0'): Server
{
    $server = new Server($host, $port);

    $server->on('request', function ($request, $response) use ($handler) {
        Container\set(DID_MATCH, false);
        Container\set(SWOOLE_HTTP_REQUEST_ENDED, false);
        Container\set(SWOOLE_HTTP_REQUEST, $request);
        Container\set(SWOOLE_HTTP_RESPONSE, $response);

        return $handler($request, $response);
    });

    return $server;
}

/**
 * Gets the current Swoole HTTP request.
 */
function request(): Request
{
    return Container\get(SWOOLE_HTTP_REQUEST);
}

/**
 * Gets the current Swoole HTTP response.
 */
function response(): Response
{
    return Container\get(SWOOLE_HTTP_RESPONSE);
}

/**
 * Controls Swoole halting avoiding calling end() more than once.
 *
 * @param string $content Content for the output.
 * @param int $status HTTP response status code.
 * @param array $headers HTTP response headers.
 *
 * @return null
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

    response()->end($content);

    return null;
}

/**
 * Sugar to emit() JSON encoded data.
 *
 * @param mixed $data
 * @param int $status
 * @param array $headers
 *
 * @return null
 */
function json($data, int $status = 200, array $headers = [])
{
    $content = json_encode($data);

    if (false === $content) {
        $error = json_last_error_msg();

        throw new UnexpectedValueException($error);
    }

    $headers = array_merge(['Content-Type' => 'application/json'], $headers);

    return emit($content, $status, $headers);
}

/**
 * Attach hooks for Swoole WebSocket server events.
 * `open` => Called when a client connects to the server.
 * `close` => Called when a client disconnects from the server.
 *
 * @param array $hooks The hooks to be attached.
 */
function websocket_hooks(array $hooks)
{
    if (array_key_exists('open', $hooks)) {
        Container\set(SWOOLE_WEBSOCKET_ONOPEN, $hooks['open']);
    }

    if (array_key_exists('close', $hooks)) {
        Container\set(SWOOLE_WEBSOCKET_ONCLOSE, $hooks['close']);
    }
}

/**
 * Returns a Swoole\WebSocket\Server.
 *
 * @param callable $handler The handler to call on each message.
 * @param int $port The port binding (defaults to 9502).
 * @param string $host The host binding (defaults to 0.0.0.0).
 *
 * @return WebsocketServer
 */
function websocket(callable $handler, int $port = 9502, string $host = '0.0.0.0'): WebsocketServer
{
    $server = new WebsocketServer($host, $port);
    Container\set(SWOOLE_WEBSOCKET_SERVER, $server);

    $server->on('open', function ($server, $request) {
        $onOpen = Container\get(SWOOLE_WEBSOCKET_ONOPEN);

        if (!is_null($onOpen)) {
            $onOpen($request, $server);
        }
    });

    $server->on('message', function (WebsocketServer $server, Frame $frame) use ($handler) {
        return $handler($frame, $server);
    });

    $server->on('close', function ($server, $fd) {
        $onClose = Container\get(SWOOLE_WEBSOCKET_ONCLOSE);

        if (!is_null($onClose)) {
            $onClose($fd, $server);
        }
    });

    return $server;
}

/**
 * Pushes a message to a specific websocket client.
 *
 * @param string $message
 * @param int $fd
 *
 * @return mixed
 */
function push(string $message, int $fd)
{
    if (!Container\has(SWOOLE_WEBSOCKET_SERVER)) {
        throw new OutOfBoundsException('There is no server to push.');
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
        throw new OutOfBoundsException('There is no server to broadcast.');
    }

    $server = Container\get(SWOOLE_WEBSOCKET_SERVER);

    foreach ($server->connections as $fd) {
        push($message, $fd);
    }
}

/**
 * Enable CORS in a Swoole Response.
 *
 * @param string $origin Comma-separated list of allowed origins, defaults to "*".
 * @param string $headers Comma-separated list of allowed headers, defaults to "Content-Type".
 * @param string $methods Comma-separated list of allowed methods, defaults to "GET, POST, PUT, DELETE".
 */
function cors(string $origin = '*', string $headers = 'Content-Type', string $methods = 'GET, POST, PUT, DELETE')
{
    $response = Container\get(SWOOLE_HTTP_RESPONSE);

    $response->header('Access-Control-Allow-Origin', $origin);
    $response->header('Access-Control-Allow-Headers', $headers);
    $response->header('Access-Control-Allow-Methods', $methods);

    $request = Container\get(SWOOLE_HTTP_REQUEST);

    if ('OPTIONS' === $request->server['request_method']) {
        emit('');
    }
}

/**
 * Sugar to Swoole`s Http Request rawContent().
 *
 * @return string
 */
function raw(): string
{
    $content = Container\get(SWOOLE_HTTP_REQUEST)->rawContent();

    if (empty($content)) {
        return '';
    }

    return $content;
}

/**
 * Sugar for HTTP 204 No Content.
 */
function no_content()
{
    emit('', 204);
}

/**
 * Creates and handles GraphQL subscriptions messages over Swoole WebSockets.
 *
 * @param SubscriptionsManager $manager
 * @param int $port
 * @param string $host
 *
 * @return WebsocketServer
 */
function graphql_subscriptions(SubscriptionsManager $manager, int $port = 3000, string $host = '0.0.0.0'): WebsocketServer
{
    $workers = new Table(1024);
    $workers->column('id', Table::TYPE_INT);
    $workers->create();

    $handle = function (array $message, int $fd) use ($manager) {
        $conn = new GraphQLSubscriptionsConnection($fd);
        $manager->handle($conn, $message);
    };

    $handler = function (Frame $frame, WebsocketServer $server) use ($workers, $handle) {
        $message = Json\decode($frame->data);
        $handle($message, $frame->fd);

        if ($message['type'] === GQL_DATA) {
            foreach ($workers as $worker) {
                if ($worker['id'] !== $server->worker_id) {
                    $server->sendMessage($frame->data, $worker['id']);
                }
            }
        }
    };

    $server = websocket($handler, $port, $host);
    $server->set(['websocket_subprotocol' => WEBSOCKET_SUB_PROTOCOL]);

    $server->on('workerStart', function (WebsocketServer $_, int $workerId) use ($workers) {
        $workers[$workerId] = ['id' => $workerId];
    });

    $server->on('pipeMessage', function (WebsocketServer $server, int $_, string $message) use ($handle) {
        $message = Json\decode($message);

        foreach ($server->connections as $fd) {
            $handle($message, $fd);
        }
    });

    return $server;
}
