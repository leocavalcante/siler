# Routing PSR-7 Requests

With Siler is possible to link a PSR-7 `ServerRequestInterface` object and make routes work with it instead of _superglobals_. I will let a example talk, but leave a message on any doubts. We are using [zend-diactoros](https://zendframework.github.io/zend-diactoros/) for the implementation.

```php
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
```
