<?php

use Siler\Diactoros;
use Siler\Functional as F;
use Siler\Route;
use Siler\Twig;

chdir(dirname(dirname(__DIR__)));
require 'vendor/autoload.php';

Twig\init('examples/react-http');

$loop = React\EventLoop\Factory::create();

$server = new React\Http\Server(function (Psr\Http\Message\ServerRequestInterface $request) {
    Route\psr7($request);

    $response = Diactoros\text('not found', 404);
    $response = Route\get('/baz', 'examples/react-http/baz.php') ?? $response;
    $response = Route\get('/foo', F\always(Diactoros\text('foo'))) ?? $response;
    $response = Route\get('/', F\always(Diactoros\text('home'))) ?? $response;

    return $response;
});

$socket = new React\Socket\Server(8080, $loop);
$server->listen($socket);

echo "Server running at http://127.0.0.1:8080\n";

$loop->run();