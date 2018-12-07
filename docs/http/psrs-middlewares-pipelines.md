# PSR-7, PSR-15, Middlewares & Pipelines

Siler embraces community standards and tools. For HTTP abstraction, the PHP Framework Interop Group recommends HTTP message interfaces (PSR-7) and HTTP Server Request Handlers (PSR-15).

This interfaces are already (and amazingly) implemented by Zend, in the projects: Diactoros and Stratigility. Siler wrapps them and exposes a function-friendy API handling state internally while achiving a fully-featured and declarative way to: **Pipeline Middlewares**.

## Usage

### Hello World

A very simple example:

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
| --- | --- |
| `pipe` | Creates a Stratigility `MiddlewarePipe` with a default name and pipes the given Clousure to it already wrapping it inside a `MiddlewareInterface` decorator, or you can pass any implementation `MiddlewareInterface` to it. |
| `text` | Creates a Diactoros `TextResponse`. The Diactoros namespace in Siler is basically just helper functions for Responses. |
| `sapi_emit` | Creates and immediatly calls `emit` method on a HttpHandlerRunner `SapiEmitter`.
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
$response = Route\match([
    Route\get('/', $homeHandler, $request),
    Route\get('/admin', Stratigility\process($request, 'auth')($adminHandler), $request),
    Route\get('/secret', Stratigility\process($request, 'auth')($secretHandler), $request),
    Diactoros\json('not found', 404),
]);

HttpHandlerRunner\sapi_emit($response);
```

The second argument on `pipe` here is a Pipeline name, you can pipe middlewares to any number of pipelines, then in `Stratigility\process` we marshal it, from the given `$request` and returns a Clousure to be called on a final handler.