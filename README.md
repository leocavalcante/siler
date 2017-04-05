[![Build Status](https://travis-ci.org/leocavalcante/siler.svg?branch=master)](https://travis-ci.org/leocavalcante/siler)
[![codecov](https://codecov.io/gh/leocavalcante/siler/branch/master/graph/badge.svg)](https://codecov.io/gh/leocavalcante/siler)
[![StyleCI](https://styleci.io/repos/75350712/shield)](https://styleci.io/repos/75350712)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)
[![Latest Unstable Version](https://poser.pugx.org/leocavalcante/siler/v/unstable)](//packagist.org/packages/leocavalcante/siler)
[![License](https://poser.pugx.org/leocavalcante/siler/license)](https://packagist.org/packages/leocavalcante/siler)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/703f233e-0738-4bf3-9d47-09d3c6de19b0/mini.png)](https://insight.sensiolabs.com/projects/703f233e-0738-4bf3-9d47-09d3c6de19b0)

# Siler

Siler is a set of general purpose high-level abstractions aiming an API for declarative programming in PHP.

* 💧 **Files and functions** as first-class citizens
* 🔋 **Zero dependency**, everything is on top of PHP built-in functions
* ⚡ **Blazing fast**, no additional overhead - [*benchmark*](https://github.com/kenjis/php-framework-benchmark#results)

### Getting Started

#### Installation

```bash
$ composer require leocavalcante/siler dev-master
```

That is it. Actually, Siler is a library, not a framework (maybe a micro-framework), the overall program flow of control is dictated by you. So, no hidden configs or predefined directory structures.

##### Or you can start by bootstrapping

```bash
$ composer create-project siler/project hello-siler
```
It's a minimal project template, just with Siler and a convenient `serve` script:
```bash
$ cd hello-siler/
$ composer serve
```

#### Hello World

```php
use Siler\Functional as λ;
use Siler\Route;

Route\get('/', λ\puts('Hello World'));
```
Nothing more, nothing less. You don't need even tell Siler to `run` or something like that.

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

#### Ratchet

Real-time web apps using WebSockets.

```bash
$ composer require cboden/ratchet
```

```php
use Siler\Ratchet;

Ratchet\connected(function ($conn) {
    print("New connection\n");
});

Ratchet\inbox(function ($from, $message) {
    printf("New message: %s\n", $message);
});

print("Listen on 3333\n");
Ratchet\init(3333);
```

#### GraphQL

[A query language for your API](http://graphql.org/).

```bash
$ composer require webonyx/graphql-php
```

```php
use Siler\Graphql;

$query = Graphql\type('Query')([
    Graphql\str('foo')(function ($root, $args) {
        return 'bar'.$root['baz'];
    }),
]);

$mutation = Graphql\type('Mutation')([
    Graphql\int('sum')(function ($root, $args) {
        return $args['x'] + $args['y'];
    }, [Graphql\int('x')(), Graphql\int('y')()])
]);

$root = ['baz' => 'qux'];

Graphql\init(new \GraphQL\Schema(['query' => $query(), 'mutation' => $mutation()]), $root);
```

---
More action here: [siler-examples](https://github.com/leocavalcante/siler-examples)

⚠️️ **This is a work in progress, API may change** 🚧

But if you give a try, I'd love the get some feedback

MIT 2017
