<p align="center">
    <br><br>
    <img src="siler.png" height="100"/>
    <br><br><br><br><br><br>
</p>

[![Build](https://github.com/leocavalcante/siler/workflows/CI/badge.svg)](https://github.com/leocavalcante/siler/actions)
[![codecov](https://codecov.io/gh/leocavalcante/siler/branch/master/graph/badge.svg)](https://codecov.io/gh/leocavalcante/siler)
[![Psalm coverage](https://shepherd.dev/github/leocavalcante/siler/coverage.svg?)](https://shepherd.dev/github/leocavalcante/siler)
[![Latest Stable Version](https://poser.pugx.org/leocavalcante/siler/v/stable)](https://packagist.org/packages/leocavalcante/siler)
[![Total Downloads](https://poser.pugx.org/leocavalcante/siler/downloads)](https://packagist.org/packages/leocavalcante/siler)
[![License](https://poser.pugx.org/leocavalcante/siler/license)](https://packagist.org/packages/leocavalcante/siler)

Siler is a set of general purpose high-level abstractions aiming an API for declarative programming in PHP.

* 💧 **Files and functions** as first-class citizens
* 🔋 **Zero dependency**, everything is on top of PHP built-in functions
* ⚡ **Blazing fast**, no additional overhead - [*benchmark A*](https://github.com/kenjis/php-framework-benchmark#results) and [*benchmark B*](https://qiita.com/prograti/items/01eac3d20f1447a7b2f9)

## Use with [Swoole](https://www.swoole.co.uk/)

Flat files and plain-old PHP functions rocking on a production-grade, high-performance, scalable, concurrent and non-blocking HTTP server.

[Read the tutorial.](https://siler.leocavalcante.com/swoole)

## Getting Started

### Installation

```bash
$ composer require leocavalcante/siler
```

That is it. Actually, Siler is a library, not a framework (maybe a micro-framework), the overall program flow of control is dictated by you. So, no hidden configs or predefined directory structures.

### Hello World

```php
use function Siler\{Functional\puts, Route\get};

get('/', puts('hello world'));
```
Nothing more, nothing less. You don't need even tell Siler to `run` or something like that (`puts` works like a lazily evaluated `echo`).

As said before, Siler aims to use PHP files and functions as first-class citizens, so no Controllers here. If you want to call something more self-container instead of a Closure, you can simply give a PHP filename then Siler will require it for you.

<sub>index.php</sub>
```php
use Siler\Route;

Route\get('/', 'pages/home.php');
```

<sub>pages/home.php</sub>
```php
echo 'Hello World';
```

### Namespaces

Siler doesn't try to be a fully-featured framework - don't even aim to be a framework - instead it embraces component based architectures and offers helper functions to work with this components under PHP namespaces.

#### Twig

Is one of the libraries that has helpers functions making work with templates quite simple.

```bash
$ composer require twig/twig
```

```php
use Siler\Functional as F;
use Siler\Route;
use Siler\Twig;

Twig\init('path/to/templates');
Route\get('/', F\puts(Twig\render('template.twig')));
```

#### Dotenv

Siler also brings helper functions for [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv), so you can easily acomplish [twelve-factor](https://12factor.net/) apps.

```bash
$ composer require vlucas/phpdotenv
```

<sub>.env</sub>
```ini
TWIG_DEBUG=true
```

<sub>index.php</sub>
```php
use Siler\Dotenv;
use Siler\Route;
use Siler\Twig;

Dotenv\init('path/to/.env');
Twig\init('path/to/templates', 'path/to/templates/cache', Dotenv\env('TWIG_DEBUG'));
Route\get('/', 'pages/home.php');
```

#### Monolog

Monolog sends your logs to files, sockets, inboxes, databases and various web services. See the complete list of handlers [here](https://github.com/Seldaek/monolog/blob/master/doc/02-handlers-formatters-processors.md#handlers). Special handlers allow you to build advanced logging strategies.

```bash
$ composer require monolog/monolog
```

```php
use Siler\Monolog as Log;

Log\handler(Log\stream(__DIR__.'/siler.log'));

Log\debug('debug', ['level' => 'debug']);
Log\info('info', ['level' => 'info']);
Log\notice('notice', ['level' => 'notice']);
Log\warning('warning', ['level' => 'warning']);
Log\error('error', ['level' => 'error']);
Log\critical('critical', ['level' => 'critical']);
Log\alert('alert', ['level' => 'alert']);
Log\emergency('emergency', ['level' => 'emergency']);
```

#### GraphQL

[A query language for your API](http://graphql.org/). Thanks to webonyx/graphql-php you can build you Schema from a
type definitions string and thanks to Siler you can tie them to resolvers:

```bash
$ composer require webonyx/graphql-php
```

<sub>schema.graphql</sub>
```graphql
type Query {
  message: String
}

type Mutation {
  sum(a: Int, b: Int): Int
}
```

<sub>index.php</sub>
```php
use Siler\GraphQL;
use Siler\Http\Response;

// Enable CORS for GraphiQL
Response\header('Access-Control-Allow-Origin', '*');
Response\header('Access-Control-Allow-Headers', 'content-type');

$typeDefs = file_get_contents('path/to/schema.graphql');

$resolvers = [
    'Query' => [
        'message' => 'foo',
    ],
    'Mutation' => [
        'sum' => function ($root, $args) {
            return $args['a'] + $args['b'];
        },
    ],
];

GraphQL\init(GraphQL\schema($typeDefs, $resolvers));
```

---

## License

[![License](http://img.shields.io/:license-mit-blue.svg?style=flat-square)](http://badges.mit-license.org)

- **[MIT license](http://opensource.org/licenses/mit-license.php)**
- Copyright 2020 © <a href="https://leocavalcante.dev" target="_blank">LC</a>.
