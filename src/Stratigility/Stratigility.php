<?php

declare(strict_types=1);

namespace Siler\Stratigility;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Siler\Container;
use UnexpectedValueException;
use Zend\Stratigility\MiddlewarePipe;
use function Zend\Stratigility\middleware;

const DEFAULT_STRATIGILITY_PIPELINE = 'default_stratigility_pipeline';

/**
 * Process a pipeline wrapped on a Siler's route.
 *
 * @param ServerRequestInterface $request The PSR-7 request.
 * @param string $name The pipeline name.
 *
 * @return Closure
 *
 * @psalm-return Closure(callable):Closure(array):ResponseInterface
 */
function process(ServerRequestInterface $request, string $name = DEFAULT_STRATIGILITY_PIPELINE): Closure
{
    $pipeline = Container\get($name);

    if (is_null($pipeline) || !($pipeline instanceof MiddlewarePipe)) {
        throw new UnexpectedValueException("MiddlewarePipe with name $name not found");
    }

    return function (callable $handler) use ($pipeline, $request) {
        return function (array $pathParams) use ($pipeline, $request, $handler) {
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
    $pipeline = Container\get($name);

    if (is_null($pipeline)) {
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
    $pipeline = Container\get($name);

    if (is_null($pipeline) || !($pipeline instanceof MiddlewarePipe)) {
        throw new UnexpectedValueException("MiddlewarePipe with name $name not found");
    }

    return $pipeline->handle($request);
}
