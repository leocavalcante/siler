<?php

declare(strict_types=1);
require_once __DIR__.'/../../../vendor/autoload.php';

use function Siler\Diactoros\request;
use function Siler\Diactoros\text;
use function Siler\HttpHandlerRunner\sapi_emit;
use function Siler\Stratigility\handle;
use function Siler\Stratigility\pipe;

pipe(function ($request, $handler) {
    return text('hello world');
});

sapi_emit(handle(request()));
