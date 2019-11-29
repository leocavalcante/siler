<?php

declare(strict_types=1);

/*
 * Siler module to work with Swoole.
 */

namespace Siler\Swoole;

use Closure;
use Exception;
use GraphQL\Error\FormattedError;
use GraphQL\Type\Schema;
use OutOfBoundsException;
use Siler\Container;
use Siler\Encoder\Json;
use Siler\GraphQL\SubscriptionsManager;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server as HttpServer;
use Swoole\Server;
use Swoole\Server\Port as ServerPort;
use Swoole\Table;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebsocketServer;
use Throwable;
use function Siler\array_get;
use function Siler\GraphQL\execute;
use const Siler\GraphQL\GQL_DATA;
use const Siler\GraphQL\GRAPHQL_DEBUG;
use const Siler\GraphQL\WEBSOCKET_SUB_PROTOCOL;
use const Siler\Route\DID_MATCH;

const SWOOLE_HTTP_REQUEST = 'swoole_http_request';
const SWOOLE_HTTP_REQUEST_ENDED = 'swoole_http_request_ended';
const SWOOLE_HTTP_RESPONSE = 'swoole_http_response';
const SWOOLE_WEBSOCKET_SERVER = 'swoole_websocket_server';
const SWOOLE_WEBSOCKET_ONOPEN = 'swoole_websocket_onopen';
const SWOOLE_WEBSOCKET_ONCLOSE = 'swoole_websocket_onclose';

/**
 * @return Closure
 *
 * @psalm-return \Closure(Request, Response):mixed
 */
function http_handler(callable $handler): Closure
{
    return function (Request $request, Response $response) use ($handler) {
        Container\set(DID_MATCH, false);
        Container\set(SWOOLE_HTTP_REQUEST_ENDED, false);
        Container\set(SWOOLE_HTTP_REQUEST, $request);
        Container\set(SWOOLE_HTTP_RESPONSE, $response);

        return $handler($request, $response);
    };
}

/**
 * Returns a Swoole HTTP server.
 *
 * @param callable $handler The callable to call on each request.
 * @param int $port The port binding (defaults to 9501).
 * @param string $host The host binding (defaults to 0.0.0.0).
 *
 * @return HttpServer
 */
function http(callable $handler, int $port = 9501, string $host = '0.0.0.0'): HttpServer
{
    $server = new HttpServer($host, $port);
    $server->on('request', http_handler($handler));

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
 * @throws Exception
 */
function json($data, int $status = 200, array $headers = [])
{
    $content = Json\encode($data);
    $headers = array_merge(['Content-Type' => 'application/json'], $headers);

    return emit($content, $status, $headers);
}

/**
 *  Attach hooks for Swoole WebSocket server events.
 *  `open` => Called when a client connects to the server.
 *  `close` => Called when a client disconnects from the server.
 *
 * @param array $hooks The hooks to be attached.
 *
 * @return void
 */
function websocket_hooks(array $hooks): void
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
 *  Broadcasts a message to every websocket client.
 *
 * @param string $message
 *
 * @return void
 */
function broadcast(string $message): void
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
 *  Enable CORS in a Swoole Response.
 *
 * @param string $origin Comma-separated list of allowed origins, defaults to "*".
 * @param string $headers Comma-separated list of allowed headers, defaults to "Content-Type, Authorization".
 * @param string $methods Comma-separated list of allowed methods, defaults to "GET, POST, PUT, DELETE".
 *
 * @return void
 */
function cors(string $origin = '*', string $headers = 'Content-Type, Authorization', string $methods = 'GET, POST, PUT, DELETE'): void
{
    $response = Container\get(SWOOLE_HTTP_RESPONSE);

    $response->header('Access-Control-Allow-Origin', $origin);
    $response->header('Access-Control-Allow-Headers', $headers);
    $response->header('Access-Control-Allow-Methods', $methods);

    $request = Container\get(SWOOLE_HTTP_REQUEST);

    if ('OPTIONS' === $request->server['request_method']) {
        no_content();
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
 *  Sugar for HTTP 204 No Content.
 *
 * @param array $headers
 *
 * @return void
 */
function no_content(array $headers = []): void
{
    emit('', 204, $headers);
}

/**
 *  Sugar for HTTP 404 Not Found.
 *
 * @param string $content
 * @param array $headers
 *
 * @return void
 */
function not_found(string $content = '', array $headers = []): void
{
    emit($content, 404, $headers);
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
                    $server->sendMessage(Frame::pack($frame), $worker['id']);
                }
            }
        }
    };

    $server = websocket($handler, $port, $host);
    $server->set(['websocket_subprotocol' => WEBSOCKET_SUB_PROTOCOL]);

    $server->on('workerStart', function (WebsocketServer $unusedServer, int $workerId) use ($workers) {
        $workers[$workerId] = ['id' => $workerId];
    });

    $server->on('pipeMessage', function (WebsocketServer $unusedServer, int $unusedFromWorkerId, string $message) use ($handle) {
        /** @var Frame $frame */
        $frame = Frame::unpack($message);
        $handle(Json\decode($frame->data), $frame->fd);
    });

    return $server;
}

/**
 * Gets the Bearer token from the Authorization request header.
 *
 * @return string|null
 */
function bearer(): ?string
{
    $token = array_get(request()->header, 'authorization');

    if ($token === null) {
        return null;
    }

    $token = substr($token, 7);

    if ($token === false) {
        return null;
    }

    return $token;
}

/**
 * Creates a HTTP server from a server port.
 *
 * @param Server $server
 * @param callable $handler
 * @param int $port
 * @param string $host
 *
 * @return ServerPort
 */
function http_server_port(Server $server, callable $handler, int $port = 80, string $host = '0.0.0.0'): ServerPort
{
    $port_server = $server->addlistener($host, $port, SWOOLE_SOCK_TCP);
    $port_server->set(['open_http_protocol' => true]);
    $port_server->on('request', http_handler($handler));

    return $port_server;
}

/**
 * @return Closure
 *
 * @psalm-return \Closure():mixed
 */
function graphql_handler(Schema $schema, $rootValue = null, $context = null): Closure
{
    return function () use ($schema, $rootValue, $context) {
        try {
            $input = Json\decode(raw());
            $result = execute($schema, $input, $rootValue, $context);
        } catch (Throwable $exception) {
            $result = FormattedError::createFromException($exception, Container\get(GRAPHQL_DEBUG, 0) > 0);
        } finally {
            json($result);
        }
    };
}
