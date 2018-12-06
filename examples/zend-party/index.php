<?php

declare(strict_types=1);
require_once __DIR__.'/../../vendor/autoload.php';

use function Siler\Diactoros\json;
use function Siler\Diactoros\request;
use function Siler\Http\Request\get;
use function Siler\HttpHandlerRunner\sapi_emit;
use function Siler\Stratigility\handle;
use function Siler\Stratigility\pipe;
use function Zend\Stratigility\middleware;

// A PSR-15 Middleware
$greet = function ($req, $handler) {
    $name = get('name', 'world');
    return $handler->handle($req->withAttribute('name', $name));
};

// A PSR-15 Middleware
$attach = function ($req, $handler) {
    return json(['hello' => $req->getAttribute('name')]);
};

// Stratigility default pipeline
pipe(middleware($greet));
pipe(middleware($attach));

// A PSR-7 Server Request from Globals
$req = request();

// A PSR-7 Response Message after pipline marshaling
$res = handle($req);

// Standard API emitter (header, echo, http_status_code)
sapi_emit($res);

/**
 * > curl http://localhost:8080
 * < {"hello":"world"}
 */

/**
 * > curl http://localhost:8080?name=leo
 * < {"hello":"leo"}
 */
