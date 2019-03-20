<?php

declare(strict_types=1);

chdir(dirname(dirname(__DIR__)));
require_once 'vendor/autoload.php';

use Siler\Diactoros;
use Siler\HttpHandlerRunner;
use Siler\Route;
use function Siler\array_get;

$request = Diactoros\request();
$response = Route\match([
    // /greet/Leo?salute=Hello
    Route\get(
        '/greet/{name}',
        function ($params) use ($request) {
            $salute = array_get($request->getQueryParams(), 'salute', 'Olá');

            return Diactoros\text("{$salute} {$params['name']}");
        },
        $request
    ),

    Route\get(
        '/',
        function () {
            return Diactoros\text('hello world');
        },
        $request
    ),

    Diactoros\text('not found', 404)
]);

HttpHandlerRunner\sapi_emit($response);
