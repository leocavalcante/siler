# PSR-7: HTTP message interfaces

Siler supports design decisions provided by the PHP community. In addition to style choices like PSR-2 and consistency like PSR-4, for HTTP abstraction you can use the PSR-7 with the help of [zend-diactoros](https://github.com/zendframework/zend-diactoros).

```bash
$ composer require zendframework/zend-diactoros
```

You can create a _superglobals_ seeded `ServerRequest` with `Siler\Diactoros\request()`:

```php
use Siler\Diactoros;

$request = Diactoros\request();
```

And create Responses through this helpers:

```php
$json = Diactoros\json(['some' => 'data']);
$html = Diactoros\html('<p>some markup</p>');
$text = Diactoros\text('plain text');
```

If none of them fits your needs, you can create a raw Response:

```php
$response = Diactoros\response();
$response->getBody()->write('something');
```

To emit a Response, there is no big deal, if you got Siler, already imagined that is also about a function call, but this time we get the help from `HttpHandlerRunner`:

```php
HttpHandlerRunner\sapi_emit($response);
```

As in `Siler\Http\Response` functions, the `HttpHandlerRunner\sapi_emit` will output headers and text to the buffer, so use carefully.

Example:

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
