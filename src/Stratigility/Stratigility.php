<?php

declare(strict_types=1);

namespace Siler\Stratigility;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Siler\Container;
use Zend\Stratigility\MiddlewarePipe;

const DEFAULT_STRATIGILITY_PIPELINE = 'default_stratigility_pipeline';

/**
 * Creates a new Stratigility pipeline.
 *
 * @param string $name The pipeline name used by the Siler\Container.
 *
 * @return MiddlewarePipe
 */
function pipeline(string $name = DEFAULT_STRATIGILITY_PIPELINE): MiddlewarePipe
{
    $pipeline = new MiddlewarePipe();
    Container\set($name, $pipeline);

    return $pipeline;
}

/**
 * Adds a MiddlewareInterface to a pipeline.
 *
 * @param MiddlewareInterface $middleware The given middleware.
 * @param string              $name       The pipeline name stored in Siler\Container.
 *
 * @return MiddlwarePipe
 */
function pipe(MiddlewareInterface $middleware, string $name = DEFAULT_STRATIGILITY_PIPELINE): MiddlewarePipe
{
    $pipeline = Container\get($name);

    if (is_null($pipeline)) {
        $pipeline = pipeline($name);
    }

    $pipeline->pipe($middleware);

    return $pipeline;
}

/**
 * Calls handle on the given MiddlewarePipe.
 *
 * @param ServerRequestInterface $request The Request message to be handled.
 * @param string                 $name    The pipeline name on Siler\Container.
 *
 * @return ResponseInterface
 */
function handle(ServerRequestInterface $request, string $name = DEFAULT_STRATIGILITY_PIPELINE) : ResponseInterface
{
    $pipeline = Container\get($name);

    if (is_null($pipeline) || !($pipeline instanceof MiddlewarePipe)) {
        throw new \UnexpectedValueException("MiddlewarePipe with name $name not found");
    }

    return $pipeline->handle($request);
}
