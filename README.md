<p align="center">
    <br><br><br><br>    
    <img src="siλer.png" height="100"/>
    <br><br><br><br>
</p>

[![Build Status](https://travis-ci.org/leocavalcante/siler.svg?branch=master)](https://travis-ci.org/leocavalcante/siler)
[![codecov](https://codecov.io/gh/leocavalcante/siler/branch/master/graph/badge.svg)](https://codecov.io/gh/leocavalcante/siler)
[![StyleCI](https://styleci.io/repos/75350712/shield)](https://styleci.io/repos/75350712)
[![Latest Stable Version](https://poser.pugx.org/leocavalcante/siler/v/stable)](https://packagist.org/packages/leocavalcante/siler)
[![Total Downloads](https://poser.pugx.org/leocavalcante/siler/downloads)](https://packagist.org/packages/leocavalcante/siler)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/703f233e-0738-4bf3-9d47-09d3c6de19b0/mini.png)](https://insight.sensiolabs.com/projects/703f233e-0738-4bf3-9d47-09d3c6de19b0)

Siler is a set of general purpose high-level abstractions aiming an API for declarative programming in PHP.

* 💧 **Files and functions** as first-class citizens
* 🔋 **Zero dependency**, everything is on top of PHP built-in functions
* ⚡ **Blazing fast**, no additional overhead - [*benchmark*](https://github.com/kenjis/php-framework-benchmark#results)

### Getting Started

[![Join the chat at https://gitter.im/leocavalcante/siler](https://badges.gitter.im/leocavalcante/siler.svg)](https://gitter.im/leocavalcante/siler?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

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
use Siler\Graphql;
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

Graphql\init(Graphql\schema($typeDefs, $resolvers));
```

---

MIT 2018
