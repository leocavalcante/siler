# Routing PSR-7 Requests

With Siler is possible to pass a PSR-7 `ServerRequestInterface` object and make routes work with it instead of _superglobals_. I will let a example talk, but leave a message on any doubts. We are using [zend-diactoros](https://zendframework.github.io/zend-diactoros/) for the PSR-7 implementation and [zend-httphandlerrunner](https://zendframework.github.io/zend-httphandlerrunner/) for SAPI emit (http_status_code, header, echo).

```php
<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

use Siler\Diactoros;
use Siler\HttpHandlerRunner;
use Siler\Route;
use function Siler\array_get;

$request = Diactoros\request();
$response = Route\match([
    // /greet/Leo?salute=Hello
    Route\get('/greet/{name}', function ($params) use ($request) {
        $salute = array_get($request->getQueryParams(), 'salute', 'Olá');
        return Diactoros\text("{$salute} {$params['name']}");
    }, $request),

    Route\get('/', function () {
        return Diactoros\text('hello world');
    }, $request),

    Diactoros\text('not found', 404),
]);

HttpHandlerRunner\sapi_emit($response);
```
