# PSRs & Middleware Pipeline

This interfaces are already \(and amazingly\) implemented by Laminas at projects: [Diactoros](https://github.com/laminas/laminas-diactoros) and [Stratigility](https://github.com/laminas/laminas-stratigility). Siler wraps them and exposes a function-friendly API handling state internally while achieving a fully-featured and declarative way for: **Middleware Pipelining**.

## PSR-7 HTTP Messages

{% hint style="info" %}
Siler doesn't have direct dependencies, to stay fit, it favors peer dependencies, which means you have to explicitly declare a `diactoros` dependency in your project in order to use it.
{% endhint %}

```bash
composer require laminas/laminas-diactoros
```

You can create a _superglobals_ seeded `ServerRequest` with `Siler\Diactoros\request()`:

```php
use Siler\Diactoros;

$request = Diactoros\request();
```

And create Responses through helpers:

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

To emit a Response, there is no big deal, if you got Siler, you already imagined that is about one or two function calls, but this time we get the help from [HttpHandlerRunner](https://github.com/laminas/laminas-httphandlerrunner):

```bash
composer require laminas/laminas-httphandlerrunner
```

Then

```php
HttpHandlerRunner\sapi_emit($response);
```

As in `Siler\Http\Response` namespace functions, the `HttpHandlerRunner\sapi_emit` will output headers and text to the buffer, use it carefully.

Example:

```php
<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

use Siler\Diactoros;
use Siler\HttpHandlerRunner;
use Siler\Route;
use function Siler\array_get;

$request = Diactoros\request();
$response = Route\matching([
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

## PSR-15 Middleware Pipelining

```bash
composer require laminas/laminas-stratigility
```

{% hint style="info" %}
Siler doesn't have direct dependencies, to stay fit, it favors peer dependencies, which means you have to explicitly declare a `stratigility` dependency in your project in order to use it.
{% endhint %}

A very simple Hello World example:

```php
use function Siler\Diactoros\request;
use function Siler\Diactoros\text;
use function Siler\HttpHandlerRunner\sapi_emit;
use function Siler\Stratigility\handle;
use function Siler\Stratigility\pipe;

pipe(function ($request, $handler) {
    return text('hello world');
});

sapi_emit(handle(request()));
```

It's more `use`s than actual code because Siler is abstracting all the way down for you.

| API | Description |
| :--- | :--- |
| `pipe` | Creates a Stratigility `MiddlewarePipe` with a default name and pipes the given Clousure to it already wrapping it inside a `MiddlewareInterface` decorator, or you can pass any implementation `MiddlewareInterface` to it. |
| `text` | Creates a Diactoros `TextResponse`. The Diactoros namespace in Siler is basically just helper functions for Responses. |
| `sapi_emit` | Creates and immediately calls `emit` method on a HttpHandlerRunner `SapiEmitter`. |
| `handle` | Calls `handle` on a `MiddlewarePipe` marshaling the Request. |
| `request` | Creates a Diactoros `ServerRequest` using PHP's Globals. |

### Siler's Routes

You can also run pipelines for specific routes:

```php
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
$response = Route\matching([
    Route\get('/', $homeHandler, $request),
    Route\get('/admin', Stratigility\process($request, 'auth')($adminHandler), $request),
    Route\get('/secret', Stratigility\process($request, 'auth')($secretHandler), $request),
    Diactoros\json('not found', 404),
]);

HttpHandlerRunner\sapi_emit($response);
```

The second argument on `pipe` here is a Pipeline name, you can pipe middlewares to any number of pipelines, then in `Stratigility\process` we marshal it, from the given `$request` and returns a Closure to be called on a final handler.

