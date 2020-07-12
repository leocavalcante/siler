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
[![Dependabot Status](https://api.dependabot.com/badges/status?host=github&repo=leocavalcante/siler)](https://dependabot.com)

Siler is a set of general purpose high-level abstractions aiming an API for declarative programming in PHP.

* 💧 **Files and functions** as first-class citizens
* 🔋 **Zero dependency**, everything is on top of PHP built-in functions
* ⚡ **Blazing fast**, no additional overhead - [*benchmark 1*](https://github.com/kenjis/php-framework-benchmark#results), [*benchmark 2*](https://qiita.com/prograti/items/01eac3d20f1447a7b2f9) and [*benchmark 3*](https://github.com/the-benchmarker/web-frameworks)

## Use with [Swoole](https://www.swoole.co.uk/)

Flat files and plain-old PHP functions rocking on a production-grade, high-performance, scalable, concurrent and non-blocking HTTP server.

[Read the tutorial.](https://siler.leocavalcante.com/swoole)

## Getting started

### Installation

```bash
$ composer require leocavalcante/siler
```

That is it. Actually, Siler is a library, not a framework (maybe a micro-framework), the overall program flow of control is dictated by you. So, no hidden configs or predefined directory structures.

### Hello, World!

```php
use Siler\Functional as λ; // Just to be cool, don't use non-ASCII identifiers ;)
use Siler\Route;

Route\get('/', λ\puts('Hello, World!'));
```
Nothing more, nothing less. You don't need even tell Siler to `run` or something like that (`puts` works like a lazily evaluated `echo`).

#### JSON

```php
use Siler\Route;
use Siler\Http\Response;

Route\get('/', fn() => Response\json(['message' => 'Hello, World!']));
```

The `Response\json` function will automatically add `Content-type: application/json` in the response headers.

### Swoole

Siler provides first-class support for Swoole. You can regularly use `Route`, `Request` and `Response` modules for a Swoole HTTP server.

```php
use Siler\Http\Response;
use Siler\Route;
use Siler\Swoole;

$handler = function () {
    Route\get('/', fn() => Response\json('Hello, World!'));
};

$port = 8000;
echo "Listening on port $port\n";
Swoole\http($handler, $port)->start();
```

### GraphQL

Install peer-dependency:

```bash
composer require webonyx/graphql-php
```

#### Schema-first

```graphql
type Query {
    hello: String
}
```

```php
use Siler\Route;
use Siler\GraphQL;

$type_defs = file_get_contents(__DIR__ . '/schema.graphql');
$resolvers = [
    'Query' => [
        'hello' => fn ($root, $args, $context, $info) => 'Hello, World!'
    ]
];

$schema = GraphQL\schema($type_defs, $resolvers);

Route\post('/graphql', fn() => GraphQL\init($schema));
```

#### Code-first

Another peer-dependency:

```bash
composer require doctrine/annotations
```

Then:

```php
/**
 * @\Siler\GraphQL\Annotation\ObjectType()
 */
final class Query
{
    /**
     * @\Siler\GraphQL\Annotation\Field()
     */
    static public function hello($root, $args, $context, $info): string
    {
        return 'Hello, World!';
    }
}
```

```php
use Siler\GraphQL;
use Siler\Route;

$schema = GraphQL\annotated([Query::class]);

Route\post('/graphql', fn() => GraphQL\init($schema));
```

Object type name will be guessed from class name, same for field name, and it's return type (i.e.: PHP `string` scalar `===` GraphQL `String` scalar).

## What is next?

- [Documentation](https://siler.leocavalcante.dev/)
- [Examples](https://github.com/siler-examples)

## License

[![License](http://img.shields.io/:License-MIT-blue.svg?style=flat-square)](https://github.com/leocavalcante/siler/blob/master/LICENSE)

- **[MIT license](http://opensource.org/licenses/mit-license.php)**
- Copyright 2020 © <a href="https://leocavalcante.dev" target="_blank">LC</a>
