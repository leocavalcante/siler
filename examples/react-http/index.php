<?php

use Siler\Diactoros;
use Siler\Functional as F;
use Siler\Http;
use Siler\Route;
use Siler\Twig;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

chdir(dirname(dirname(__DIR__)));
require 'vendor/autoload.php';

Twig\init('examples/react-http');

$req = function (): ResponseInterface {
    $res = Diactoros\text('not found', 404);

    $res = Route\get('/baz', 'examples/react-http/baz.php') ?? $res;
    $res = Route\get('/foo', F\always(Diactoros\text('foo'))) ?? $res;
    $res = Route\get('/', F\always(Diactoros\text('home'))) ?? $res;

    return $res;
};

$err = function (\Exception $e) {
    var_dump($e);
    return Diactoros\text("Oops! {$e->getMessage()}", 500);
};

echo "Server running at http://127.0.0.1:8080\n";
Http\server($req, $err)()->run();