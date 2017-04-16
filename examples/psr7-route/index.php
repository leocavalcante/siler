<?php

use Siler\Diactoros;
use Siler\Route;
use function Siler\array_get;

chdir(dirname(dirname(__DIR__)));
require 'vendor/autoload.php';

$request = Diactoros\request();

Route\psr7($request);

$response = Diactoros\text('not found', 404);

// /greet/Leo?salute=Hello
$response = Route\get('/greet/{name}', function ($params) use ($request) {
    $salute = array_get($request->getQueryParams(), 'salute', 'Olá');

    return Diactoros\text("{$salute} {$params['name']}");
}) ?: $response;

$response = Route\get('/', function () {
    return Diactoros\text('hello world');
}) ?: $response;

Diactoros\emit($response);
