<?php declare(strict_types=1);
/*
 * Siler's internal MessageComponent.
 */

namespace Siler\Stratigility;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @internal Not meant to be used
 */
class RequestHandlerDecorator implements RequestHandlerInterface
{
    /** @var callable(ServerRequestInterface, array): ResponseInterface */
    private $handler;
    /** @var array */
    private $pathParams;

    public function __construct(callable $handler, array $pathParams = [])
    {
        $this->handler = $handler;
        $this->pathParams = $pathParams;
    }

    /**
     * {@inheritdoc}.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $handler = $this->handler;
        return $handler($request, $this->pathParams);
    }
}
