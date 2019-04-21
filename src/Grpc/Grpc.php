<?php declare(strict_types=1);

namespace Siler\Grpc;

use Closure;
use Google\Protobuf\Internal\Message;
use Grpc\Parser;
use ReflectionObject;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Throwable;

const SWOOLE_CLOSE = '>>>SWOOLE|CLOSE<<<';
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
    $server = new Server($host, $port, SWOOLE_BASE);
    $server->set(['open_http2_protocol' => true]);

    $server->on('request', function (Request $request, Response $response) use ($services) {
        $finish = finisher($response);

        try {
            $path = $request->server['request_uri'];
            var_dump($path);

            if ($path == SWOOLE_CLOSE) {
                return $finish(0);
            }

            $path = trim($path, '/');

            list($serviceName, $method) = explode('/', $path);

            if (!array_key_exists($serviceName, $services)) {
                return $finish(1, "$serviceName not found");
            }

            $service = $services[$serviceName];
            $serviceR = new ReflectionObject($service);
            $methodR = $serviceR->getMethod($method);
            $params = $methodR->getParameters();
            $paramR = $params[0];

            /** @var Message $message */
            $message = Parser::deserializeMessage([$paramR->getClass()->getName(), null], $request->rawContent());

            /** @var Message $reply */
            $reply = $methodR->invoke($service, $message);

            return $finish(0, '', Parser::serializeMessage($reply));
        } catch (Throwable $exception) {
            return $finish(2, $exception->getMessage());
        }
    });

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

    $response->header(':status', '200');
    $response->header('content-type', CONTENT_TYPE);
    $response->header('trailer', implode(', ', [STATUS_TRAILER, MESSAGE_TRAILER]));

    return function (int $status, string $message = '', string $content = '') use ($response) {
        $response->trailer(STATUS_TRAILER, strval($status));
        $response->trailer(MESSAGE_TRAILER, $message);

        $response->end($content);
    };
}
