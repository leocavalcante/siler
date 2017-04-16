<?php

use Psr\Http\Message\RequestInterface;
use React\EventLoop\Factory;
use React\Promise\Promise;
use React\Socket\Server;
use Siler\Diactoros;
use Siler\Functional as F;
use Siler\Route;
use Siler\Twig;

chdir(dirname(dirname(__DIR__)));
require 'vendor/autoload.php';

Twig\init('examples/react-http');

$loop = Factory::create();
$socket = new Server(isset($argv[1]) ? $argv[1] : '0.0.0.0:0', $loop);
$server = new \React\Http\Server($socket, function (RequestInterface $request) {
    return new Promise(function ($resolve, $reject) use ($request) {
        try {
            Route\psr7($request);

            $response = Diactoros\text('not found', 404);
            $response = Route\get('/baz', 'examples/react-http/baz.php') ?: $response;
            $response = Route\get('/foo', F\always(Diactoros\text('foo'))) ?: $response;
            $response = Route\get('/', F\always(Diactoros\text('home'))) ?: $response;

            $resolve($response);
        } catch (Exception $e) {
            $resolve(Diactoros\text($e->getMessage(), 500));
        }
    });
});

echo 'Listening on http://'.$socket->getAddress().PHP_EOL;

$loop->run();
