<?php declare(strict_types=1);

namespace Siler\Stratigility;

use Closure;
use Laminas\Stratigility\MiddlewarePipe;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Siler\Container;
use UnexpectedValueException;
use function Laminas\Stratigility\middleware;

const DEFAULT_STRATIGILITY_PIPELINE = 'default_stratigility_pipeline';

/**
 * Process a pipeline wrapped on a Siler's route.
 *
 * @param ServerRequestInterface $request The PSR-7 request.
 * @param string $name The pipeline name.
 * @return Closure(callable(ServerRequestInterface, array<array-key, mixed>):ResponseInterface):Closure
 */
function process(ServerRequestInterface $request, string $name = DEFAULT_STRATIGILITY_PIPELINE): Closure
{
    /** @var mixed $pipeline */
    $pipeline = Container\get($name);

    if ($pipeline === null || !($pipeline instanceof MiddlewarePipe)) {
        throw new UnexpectedValueException("MiddlewarePipe with name $name not found");
    }

    return
        /**
         * @param callable(ServerRequestInterface, array): ResponseInterface $handler
         * @return Closure
         */
        static function (callable $handler) use ($pipeline, $request) {
            return static function (array $pathParams) use ($pipeline, $request, $handler) {
                return $pipeline->process($request, new RequestHandlerDecorator($handler, $pathParams));
            };
        };
}

/**
 * Adds a MiddlewareInterface to a pipeline, creates it if not exists.
 *
 * @param MiddlewareInterface|callable $middleware The given middleware.
 * @param string $name The pipeline name stored in Siler\Container.
 *
 * @return MiddlewarePipe
 */
function pipe($middleware, string $name = DEFAULT_STRATIGILITY_PIPELINE): MiddlewarePipe
{
    /** @var MiddlewarePipe|null $pipeline */
    $pipeline = Container\get($name);

    if ($pipeline === null) {
        $pipeline = new MiddlewarePipe();
        Container\set($name, $pipeline);
    }

    if (is_callable($middleware)) {
        $middleware = middleware($middleware);
    }

    $pipeline->pipe($middleware);

    return $pipeline;
}

/**
 * Calls handle on the given MiddlewarePipe.
 *
 * @param ServerRequestInterface $request The Request message to be handled.
 * @param string $name The pipeline name on Siler\Container.
 *
 * @return ResponseInterface
 */
function handle(ServerRequestInterface $request, string $name = DEFAULT_STRATIGILITY_PIPELINE): ResponseInterface
{
    /** @var mixed $pipeline */
    $pipeline = Container\get($name);

    if ($pipeline === null || !($pipeline instanceof MiddlewarePipe)) {
        throw new UnexpectedValueException("MiddlewarePipe with name $name not found");
    }

    return $pipeline->handle($request);
}
