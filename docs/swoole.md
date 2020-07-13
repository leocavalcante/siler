---
description: >-
  Flat files and plain-old PHP functions rocking on a production-grade,
  high-performance, scalable, concurrent and non-blocking HTTP server.
---

# Siler ❤️ Swoole

## Swoole

> Enables PHP developers to write high-performance, scalable, concurrent TCP, UDP, Unix socket, HTTP, Websocket services in PHP programming language without too much knowledge about non-blocking I/O programming and low-level Linux kernel. Compared with other async programming frameworks or softwares such as Nginx, Tornado, Node.js, Swoole has the built-in async, multiple threads I/O modules. Developers can use sync or async API to write the applications — [www.swoole.co.uk](https://www.swoole.co.uk/)

### Features

* Rapid development of high performance protocol servers & clients with PHP language
* Event-driven, asynchronous programming for PHP
* Event loop API
* Processes management API
* Memory management API
* [Golang style channels](https://en.wikipedia.org/wiki/Channel_%28programming%29) for inter-processes communication

### Use cases

* Web applications and systems
* Mobile communication systems
* Online game systems
* Internet of things
* Car networking
* Smart home systems

It is open source and free. Released under the license of Apache 2.0.

## Get started

{% hint style="info" %}
Forget about everything you know on how to run PHP on web servers like Apache and Nginx behind modules and CGI layers.
{% endhint %}

Swoole is released as a [PHP extension \(PECL\)](https://pecl.php.net/package/swoole) and runs as a PHP CLI application.  
The differences between Swoole with PHP-FPM the traditional PHP model are:

* Swoole forks a number of worker processes based on CPU core number to utilize all CPU cores.
* Swoole supports Long-live connections for websocket server or TCP/UDP server.
* Swoole supports more server-side protocols.
* Swoole can manage and reuse the status in memory.

### Docker

Swoole prerequisites operation system are: Linux, FreeBSD or MacOS, but **don't worry Windows-people**, _we_ have [Docker](https://www.docker.com)! And I got us covered with [Dwoole](https://github.com/leocavalcante/dwoole):

{% code title="docker-compose.yml" %}
```yaml
version: '3'
services:
  swoole:
    container_name: siler_swoole
    image: leocavalcante/dwoole:dev
    ports:
      - '9501:9501'
    volumes:
      - ./:/app
```
{% endcode %}

{% hint style="info" %}
Beyond being cross-platform, Dwoole helps with others features like Composer and hot-restart that Unix people would also like.
{% endhint %}

## Siler

You already got Siler, right? Flat-files and plain-old PHP functions rockin'on! **Just simple**. A set of general purpose high-level abstractions aiming an API for declarative programming in PHP. **And this wouldn't be different about Swoole**.

The `Siler\Swoole` namespace get you covered.

### Hello World

{% code title="index.php" %}
```php
<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

use Siler\Swoole;

$server = fn() => Swoole\emit('Hello World');

Swoole\http($server)->start();
```
{% endcode %}

That's it! This attaches a callback handler that always emits "Hello World" on every request and starts a HTTP server on port 9501. Run it using `docker-compose up` or just `php index.php` if you're not using Docker.

Go to `http://localhost:9051` or `http://<docker_machine_ip>:9051` and you should get a "Hello World" response as plain/text.

## More Siler!

You know, Siler can do a lot more, it abstracts things like [Routing](routing.md) and [Twig Templating](twig-templating.md). Let's add this to our **Swoole** server:

{% code title="index.php" %}
```php
<?php declare(strict_types=1);
require_once 'vendor/autoload.php';

use Siler\Swoole;
use Siler\Route;

$handler = function ($req) {
    Route\get('/', 'pages/home.php');
    Swoole\emit('Not found', 404);
};

Swoole\http($handler)->start();
```
{% endcode %}

Now we are forwarding **GET** requests from path `/` to file `pages/home.php`.

{% code title="pages/home.php" %}
```php
<?php declare(strict_types=1);

use Siler\Swoole;

return fn() => Swoole\emit('Hello World');
```
{% endcode %}

{% hint style="info" %}
When using Swoole, routes that use files should return a function to ensure a re-computation. Siler will require the file **only** on the first match, then on the next matches it will only re-execute the returned function. This makes possible the use of `require_once` while maintaining a way to re-execute something.
{% endhint %}

You may ask: **"What about** `Swoole\emit('Not found', 404)` **at the end?"**.

Nice question! `Siler\Swoole\emit()` function will **short-circuit** further emit attempts, so it will work exactly like you have imagined, when a route matches a path like `/` it will emit the proper response, but when no route matches and this means: no route will emit something, then `Swoole\emit('Not found', 404)` will emit a **404 Not found** response.

Go ahead, restart the server and go to [http://localhost:9501/](http://localhost:9501/), you should still be seeing "Hello World", but going to any other path, like [http://localhost:9501/banana](http://localhost:9501/banana), you should be seeing "Not found" and a proper 404 status code.

### [Twig Templating](twig-templating.md)

Twig should work exactly the same as there is no Swoole behind it:

{% tabs %}
{% tab title="pages/home.php" %}
```php
<?php declare(strict_types=1);

use Siler\Swoole;
use Siler\Twig;

return fn() => Swoole\emit(Twig\render('home.twig'));
```
{% endtab %}

{% tab title="index.php" %}
```php
<?php declare(strict_types=1);
require_once 'vendor/autoload.php';

use Siler\Swoole;
use Siler\Route;
use Siler\Twig;

Twig\init('pages');

$handler = function ($req) {
    Route\get('/', 'pages/home.php');
    Swoole\emit('Not found', 404);
};

Swoole\http($handler)->start();
```
{% endtab %}

{% tab title="pages/home.twig" %}
```python
{% extends "_layout.twig" %}

{% block page %}
    <p>Hello World</p>
{% endblock %}
```
{% endtab %}

{% tab title="pages/\_layout.twig" %}
```markup
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Siler + Swoole</title>
</head>
<body>
    {% block page %}{% endblock %}
</body>
</html>
```
{% endtab %}
{% endtabs %}

{% hint style="info" %}
Swoole's HTTP server will auto-magically output the Response header Content-type as text/html instead of text/plain now.
{% endhint %}

If you're sure that your template doesn't depend on the request, you can render it once:

{% code title="pages/home.php" %}
```php
<?php declare(strict_types=1);

use Siler\Swoole;
use Siler\Twig;

$html = Twig\render('home.twig');

return fn() => Swoole\emit($html);
```
{% endcode %}

This avoids the template to be re-rendered on each request unnecessarily.

## Request's Query string, Body & Headers

Since there is no web server module or CGI layer, things like $\_GET won't work for query string parameters etc. **But fear nothing**, Siler provides getters for both Swoole's Request and Response objects: `Siler\Swoole\request()` and `Siler\Swoole\response()`.

Instead of always printing "Hello World", let's print the name that came from the URL parameter:

{% tabs %}
{% tab title="pages/home.php" %}
```php
<?php declare(strict_types=1);

use Siler\Swoole;
use Siler\Twig;

return function () {
    $name = Swoole\request()->get['name'] ?? 'World';
    Swoole\emit(Twig\render('home.twig', ['name' => $name]));
};
```
{% endtab %}

{% tab title="pages/home.twig" %}
```python
{% extends "_layout.twig" %}

{% block page %}
    <p>Hello {{ name }}</p>
{% endblock %}
```
{% endtab %}
{% endtabs %}

Go to [http://localhost:9501/?name=Leo](http://localhost:9501/?name=Leo), you should be seeing "Hello Leo" now.

{% hint style="info" %}
You can find more about Swoole's Request and Response objects at: [swoole.co.uk/docs/modules/swoole-http-server/methods-properties](https://www.swoole.co.uk/docs/modules/swoole-http-server/methods-properties)
{% endhint %}

## Building an API

This is as simple as **Siler** gets.  
We can add a new route/API endpoint to **GET** all of our `Todos`:

{% code title="index.php" %}
```php
<?php declare(strict_types=1);
require_once 'vendor/autoload.php';

use Siler\Route;
use Siler\Swoole;
use Siler\Twig;

Twig\init('pages');

$handler = function ($req, $res) {
    Route\get('/', 'pages/home.php');
    Route\get('/todos', 'api/todos.php');

    // None of the above short-circuited the response with Swoole\emit().
    Swoole\emit('Not found', 404);
};

Swoole\http($handler)->start();
```
{% endcode %}

Then you can return your JSON and within `json()`, Siler will automatically add the Content-type: application/json response header. Also you can enable CORS.

{% code title="api/todos.php" %}
```php
<?php declare(strict_types=1);

use Siler\Swoole;

$todos = [
    ['id' => 1, 'text' => 'foo'],
    ['id' => 2, 'text' => 'bar'],
    ['id' => 3, 'text' => 'baz'],
];

return function () use ($todos) {
    Swoole\cors();
    Swoole\json($todos);
};
```
{% endcode %}

Head to [http://localhost:9501/todos](http://localhost:9501/todos). There we go!  
A **Siler** ❤️ **Swoole** powered API.

{% hint style="info" %}
You can still use any other Swoole module like Coroutines and Redis. More abstractions to come.
{% endhint %}

