<?php

declare(strict_types=1);
require_once __DIR__ . '/../../../vendor/autoload.php';

use Siler\Diactoros;
use Siler\Http\Request;
use Siler\HttpHandlerRunner;
use Siler\Route;
use Siler\Stratigility;

$userMiddleware = function ($request, $handler) {
    $token = Request\get('token');

    if (empty($token)) {
        return Diactoros\json('no user', 401);
    }

    $user = "get_user_by_token:$token";
    $request = $request->withAttribute('user', $user);

    return $handler->handle($request);
};

$homeHandler = function () {
    return Diactoros\json('welcome');
};

$adminHandler = function ($request) {
    return Diactoros\json(['user' => $request->getAttribute('user')]);
};

$secretHandler = function ($request) {
    return Diactoros\json(['user' => $request->getAttribute('user')]);
};

Stratigility\pipe($userMiddleware, 'auth');

$request = Diactoros\request();
$response = Route\match([
    Route\get('/', $homeHandler, $request),
    Route\get('/admin', Stratigility\process($request, 'auth')($adminHandler), $request),
    Route\get('/secret', Stratigility\process($request, 'auth')($secretHandler), $request),
    Diactoros\json('not found', 404)
]);

HttpHandlerRunner\sapi_emit($response);
