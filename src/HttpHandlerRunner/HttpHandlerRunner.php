<?php

declare(strict_types=1);

namespace Siler\HttpHandlerRunner;

use Psr\Http\Message\ResponseInterface;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;


/**
 * Creates a SapiEmitter and immediatly calls its emit method.
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
