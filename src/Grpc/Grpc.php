<?php declare(strict_types=1);

namespace Siler\Grpc;

use Closure;
use Google\Protobuf\Internal\Message;
use ReflectionObject;
use RuntimeException;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Throwable;

const STATUS_TRAILER = 'grpc-status';
const MESSAGE_TRAILER = 'grpc-message';
const CONTENT_TYPE = 'application/grpc';

/**
 * @param array $services
 * @param int $port
 * @param string $host
 *
 * @return Server
 */
function server(array $services, int $port = 9090, string $host = '0.0.0.0'): Server
{
    $server = new Server($host, $port);
    $server->set([
        'enable_coroutine' => true,
        'open_http2_protocol' => true,
    ]);

    $server->on(
        'request',
        /**
         * @param Request $request
         * @param Response $response
         * @return mixed
         */
        static function (Request $request, Response $response) use ($services) {
            $finish = finisher($response);

            try {
                /**
                 * @psalm-suppress MissingPropertyType
                 * @var array<string, string> $request_server
                 */
                $request_server = $request->server;
                $path = $request_server['request_uri'];
                $path = trim($path, '/');

                list($service_name, $method) = explode('/', $path);

                if (!array_key_exists($service_name, $services)) {
                    return $finish(1, "$service_name not found");
                }

                /** @var object $service */
                $service = $services[$service_name];
                $service_r = new ReflectionObject($service);
                $method_r = $service_r->getMethod($method);
                $params = $method_r->getParameters();
                $param_r = $params[0];
                $param_class = $param_r->getClass();

                if ($param_class === null) {
                    throw new RuntimeException('Class for service message not found');
                }

                /** @var string $raw_content */
                $raw_content = $request->rawContent();

                /** @var Message $message */
                $message = Parser::deserialize($param_class, $raw_content);

                /** @var Message $reply */
                $reply = $method_r->invoke($service, $message);

                return $finish(0, '', Parser::serialize($reply));
            } catch (Throwable $exception) {
                return $finish(2, $exception->getMessage());
            }
        }
    );

    return $server;
}

/**
 * @param Response $response
 *
 * @return Closure
 * @internal
 *
 */
function finisher(Response $response): Closure
{
    $response->status(200);

    $response->header('content-type', CONTENT_TYPE);
    $response->header('content-encoding', 'identity');
    $response->header('trailer', implode(', ', [STATUS_TRAILER, MESSAGE_TRAILER]));

    return function (int $status, string $message = '', string $content = '') use ($response) {
        $response->trailer(STATUS_TRAILER, strval($status));
        $response->trailer(MESSAGE_TRAILER, $message);

        $response->end($content);
    };
}
