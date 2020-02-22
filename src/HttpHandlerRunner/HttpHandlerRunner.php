<?php

declare(strict_types=1);

namespace Siler\HttpHandlerRunner;

use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Http\Message\ResponseInterface;

/**
 * Creates a SapiEmitter and immediately calls its emit method.
 *
 * @param ResponseInterface $response The Response Message to be
 *
 * @return bool
 */
function sapi_emit(ResponseInterface $response): bool
{
    $emitter = new SapiEmitter();

    return $emitter->emit($response);
}
