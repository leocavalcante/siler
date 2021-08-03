<?php declare(strict_types=1);
/*
 * Siler module to work with Swoole.
 */

namespace Siler\Swoole;

use Closure;
use GraphQL\Error\FormattedError;
use GraphQL\Type\Schema;
use OutOfBoundsException;
use Siler\Container;
use Siler\Encoder\Json;
use Siler\GraphQL\SubscriptionsManager;
use Siler\Monolog as Log;
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
use function Siler\GraphQL\debugging;
use function Siler\GraphQL\execute;
use function Siler\GraphQL\request as graphql_request;
use const Siler\GraphQL\{GQL_DATA, WEBSOCKET_SUB_PROTOCOL};
use const Siler\Route\DID_MATCH;

const SWOOLE_HTTP_REQUEST = 'swoole_http_request';
const SWOOLE_HTTP_REQUEST_ENDED = 'swoole_http_request_ended';
const SWOOLE_HTTP_RESPONSE = 'swoole_http_response';
const SWOOLE_WEBSOCKET_SERVER = 'swoole_websocket_server';
const SWOOLE_WEBSOCKET_ONOPEN = 'swoole_websocket_onopen';
const SWOOLE_WEBSOCKET_ONCLOSE = 'swoole_websocket_onclose';

/**
 * @template T
 * @param callable(Request, Response): T $handler
 * @return Closure(Request, Response): T
 */
function http_handler(callable $handler): Closure
{
    return
        /**
         * @param Request $request
         * @param Response $response
         * @return mixed
         * @psalm-return T
         */
        function (Request $request, Response $response) use ($handler) {
            Container\set(DID_MATCH, false);
            Container\set(SWOOLE_HTTP_REQUEST_ENDED, false);
            Container\set(SWOOLE_HTTP_REQUEST, $request);
            Container\set(SWOOLE_HTTP_RESPONSE, $response);

            /**
             * @var mixed
             * @psalm-var T
             */
            return $handler($request, $response);
        };
}

/**
 * Returns a Swoole HTTP server.
 *
 * @template T
 * @param callable(Request, Response): T $handler The callable to call on each request.
 * @param int $port The port binding (defaults to 9501).
 * @param string $host The host binding (defaults to 0.0.0.0).
 * @return HttpServer
 */
function http(callable $handler, int $port = 9501, string $host = '0.0.0.0'): HttpServer
{
    $server = new HttpServer($host, $port);
    $server->on('request', http_handler($handler));

    return $server;
}

/**
 * Returns a Swoole HTTP/2 server.
 *
 * @template T
 * @param string $certFile Path to the certificate file.
 * @param string $keyFile Path to the key file.
 * @param callable(Request, Response): T $handler The callable to call on each request.
 * @param int $port The port binding (defaults to 9501).
 * @param string $host The host binding (defaults to 0.0.0.0).
 * @return HttpServer
 */
function http2(string $certFile, string $keyFile, callable $handler, int $port = 9501, string $host = '0.0.0.0'): HttpServer
{
    $server = new HttpServer($host, $port, SWOOLE_BASE, SWOOLE_SOCK_TCP | SWOOLE_SSL);
    $server->set(['ssl_cert_file' => $certFile, 'ssl_key_file' => $keyFile]);
    $server->on('request', http_handler($handler));

    return $server;
}

/**
 * Gets the current Swoole HTTP request.
 */
function request(): Request
{
    /** @var Request */
    return Container\get(SWOOLE_HTTP_REQUEST);
}

/**
 * Gets the current Swoole HTTP response.
 */
function response(): Response
{
    /** @var Response */
    return Container\get(SWOOLE_HTTP_RESPONSE);
}

/**
 * Controls Swoole halting avoiding calling end() more than once.
 *
 * @param string $content Content for the output.
 * @param int $status HTTP response status code.
 * @param array<string, string> $headers HTTP response headers.
 */
function emit(string $content, int $status = 200, array $headers = []): void
{
    /** @var bool|null $request_ended */
    $request_ended = Container\get(SWOOLE_HTTP_REQUEST_ENDED);

    if ($request_ended) {
        return;
    }

    response()->status($status);

    foreach ($headers as $key => $value) {
        response()->header($key, $value);
    }

    Container\set(SWOOLE_HTTP_REQUEST_ENDED, true);

    response()->end($content);
}

/**
 * Sugar to emit() JSON encoded data.
 *
 * @param mixed $data
 * @param int $status
 * @param array<string, string> $headers
 */
function json($data, int $status = 200, array $headers = []): void
{
    $content = Json\encode($data);
    $headers = array_merge(['Content-Type' => 'application/json'], $headers);

    emit($content, $status, $headers);
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
 * @template T
 *
 * @param callable(Frame, WebsocketServer): T $handler The handler to call on each message.
 * @param int $port The port binding (defaults to 9502).
 * @param string $host The host binding (defaults to 0.0.0.0).
 *
 * @return WebsocketServer
 *
 */
function websocket(callable $handler, int $port = 9502, string $host = '0.0.0.0'): WebsocketServer
{
    $server = new WebsocketServer($host, $port);
    Container\set(SWOOLE_WEBSOCKET_SERVER, $server);

    $server->on('open', static function (WebsocketServer $server, Request $request): void {
        /** @var callable|null $onopen */
        $onopen = Container\get(SWOOLE_WEBSOCKET_ONOPEN);

        if (is_callable($onopen)) {
            $onopen($request, $server);
        }
    });

    $server->on(
        'message',
        /**
         * @param WebsocketServer $server
         * @param Frame $frame
         *
         * @return mixed
         * @psalm-return T
         */
        static function (WebsocketServer $server, Frame $frame) use ($handler) {
            return $handler($frame, $server);
        }
    );

    $server->on('close', function (WebsocketServer $server, int $fd): void {
        /** @var callable|null $onclose */
        $onclose = Container\get(SWOOLE_WEBSOCKET_ONCLOSE);

        if (is_callable($onclose)) {
            $onclose($fd, $server);
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
 * @return void
 */
function push(string $message, int $fd): void
{
    if (!Container\has(SWOOLE_WEBSOCKET_SERVER)) {
        throw new OutOfBoundsException('There is no server to push.');
    }

    /** @var WebsocketServer $server */
    $server = Container\get(SWOOLE_WEBSOCKET_SERVER);
    $server->push($fd, $message);
}

/**
 *  Broadcasts a message to every websocket client.
 *
 * @param string $message
 */
function broadcast(string $message): void
{
    if (!Container\has(SWOOLE_WEBSOCKET_SERVER)) {
        throw new OutOfBoundsException('There is no server to broadcast.');
    }

    /** @var WebsocketServer $server */
    $server = Container\get(SWOOLE_WEBSOCKET_SERVER);

    /**
     * @psalm-suppress MissingPropertyType
     * @var int $fd
     */
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
function cors(string $origin = '*', string $headers = 'Content-Type, Authorization', string $methods = 'GET, POST, PUT, DELETE', string $credentials = 'true'): void
{
    /** @var Response $response */
    $response = Container\get(SWOOLE_HTTP_RESPONSE);

    $response->header('Access-Control-Allow-Origin', $origin);
    $response->header('Access-Control-Allow-Headers', $headers);
    $response->header('Access-Control-Allow-Methods', $methods);
    $response->header('Access-Control-Allow-Credentials', $credentials);

    /** @var Request $request */
    $request = Container\get(SWOOLE_HTTP_REQUEST);

    /**
     * @psalm-suppress MissingPropertyType
     * @var array<string, string> $request_server
     */
    $request_server = $request->server;

    if ('OPTIONS' === $request_server['request_method']) {
        no_content();
    }
}

/**
 * Sugar to Swoole`s Http Request rawContent().
 * @deprecated Use Siler\Http\Request\raw().
 */
function raw(): ?string
{
    /** @var mixed|null $content */
    $content = request()->rawContent();

    if ($content === null) {
        return null;
    }

    return (string)$content;
}

/**
 *  Sugar for HTTP 204 No Content.
 *
 * @param array<string, string> $headers
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
 * @param array<string, string> $headers
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

    $handle =
        /**
         * @param array<string, string> $message
         * @param int $fd
         */
        static function (array $message, int $fd) use ($manager): void {
            $conn = new GraphQLSubscriptionsConnection($fd);
            $manager->handle($conn, $message);
        };

    $handler = static function (Frame $frame, WebsocketServer $server) use ($workers, $handle): void {
        /**
         * @psalm-suppress MissingPropertyType
         * @var array<string, string> $message
         */
        $message = Json\decode((string)$frame->data);
        /** @psalm-suppress MissingPropertyType */
        $handle($message, (int)$frame->fd);

        if ($message['type'] === GQL_DATA) {
            /** @var array{id: int} $worker */
            foreach ($workers as $worker) {
                /** @psalm-suppress MissingPropertyType */
                if ($worker['id'] !== $server->worker_id) {
                    /** @var int $encoded_frame */
                    $encoded_frame = Frame::pack($frame);
                    /** @var string $worker_id */
                    $worker_id = $worker['id'];
                    $server->sendMessage($encoded_frame, $worker_id);
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

        /**
         * @psalm-suppress MissingPropertyType
         * @var string $frame_data
         */
        $frame_data = $frame->data;

        /**
         * @psalm-suppress MissingPropertyType
         * @var int $frame_fd
         */
        $frame_fd = $frame->fd;

        /** @var array<string, string> $decoded_frame_data */
        $decoded_frame_data = Json\decode($frame_data);

        $handle($decoded_frame_data, $frame_fd);
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
    /**
     * @psalm-suppress MissingPropertyType
     * @var array<string, string> $header
     */
    $header = request()->header;
    /** @var string|null $token */
    $token = array_get($header, 'authorization');

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
 * @param callable(Request, Response):mixed $handler
 * @param int $port
 * @param string $host
 *
 * @return ServerPort
 */
function http_server_port(Server $server, callable $handler, int $port = 80, string $host = '0.0.0.0'): ServerPort
{
    /**
     * @psalm-suppress UndefinedConstant
     * @var string $sock_type
     */
    $sock_type = SWOOLE_SOCK_TCP;

    /** @var ServerPort $server_port */
    $server_port = $server->addlistener($host, $port, $sock_type);
    $server_port->set(['open_http_protocol' => true]);
    $server_port->on('request', http_handler($handler));

    return $server_port;
}

/**
 * @template RootValue
 * @template Context
 *
 * @param Schema $schema
 * @param mixed $root_value
 * @psalm-param RootValue $root_value
 * @param mixed $context
 * @psalm-param Context $context
 *
 * @return Closure(): void
 */
function graphql_handler(Schema $schema, $root_value = null, $context = null): Closure
{
    return static function () use ($schema, $root_value, $context): void {
        $debug = debugging();

        try {
            json(execute($schema, graphql_request()->toArray(), $root_value, $context));
        } catch (Throwable $exception) {
            if ($debug > 0) {
                Log\debug('GraphQL Internal Error', ['exception' => $exception]);
            }

            json(FormattedError::createFromException($exception, $debug));
        }
    };
}

/**
 * HTTP redirect sugar.
 *
 * @param string $location
 * @param int $status
 * @param array<string, string> $headers
 * @param string $content
 */
function redirect(string $location = '/', int $status = 302, array $headers = [], string $content = 'Redirect'): void
{
    emit($content, $status, array_merge($headers, ['Location' => $location]));
}

/**
 * Calls each callback in the pipeline until one returns null.
 * The value of previous callback is given to the next, starting with null.
 *
 * @template T
 * @param array<callable(Request, Response, T|null): (T|null)> $pipeline
 * @return Closure(Request, Response): (T|null)
 */
function middleware(array $pipeline): Closure
{
    return
        /**
         * @param Request $request
         * @param Response $response
         * @return mixed|null
         * @psalm-return T|null
         */
        static function (Request $request, Response $response) use ($pipeline) {
            /**
             * @var mixed|null $value
             * @psalm-var T|null $value
             */
            $value = null;

            foreach ($pipeline as $callback) {
                if (($value = $callback($request, $response, $value)) === null) {
                    return $value;
                }
            }

            return $value;
        };
}
