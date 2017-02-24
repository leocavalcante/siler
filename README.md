# Siler

PHP files and functions as first-class citizens.

[![Build Status](https://travis-ci.org/leocavalcante/siler.svg?branch=master)](https://travis-ci.org/leocavalcante/siler)
[![codecov](https://codecov.io/gh/leocavalcante/siler/branch/master/graph/badge.svg)](https://codecov.io/gh/leocavalcante/siler)
[![StyleCI](https://styleci.io/repos/75350712/shield)](https://styleci.io/repos/75350712)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)
[![Latest Unstable Version](https://poser.pugx.org/leocavalcante/siler/v/unstable)](//packagist.org/packages/leocavalcante/siler)
[![License](https://poser.pugx.org/leocavalcante/siler/license)](https://packagist.org/packages/leocavalcante/siler)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/703f233e-0738-4bf3-9d47-09d3c6de19b0/mini.png)](https://insight.sensiolabs.com/projects/703f233e-0738-4bf3-9d47-09d3c6de19b0)

Zero dependency. Everything is built on top of PHP's built-in functions without any additional overhead, which makes it one of the [fastest *frameworks*](https://github.com/kenjis/php-framework-benchmark#results).

## Getting Started

### Installation

```bash
composer require leocavalcante/siler dev-master
```
That is it. Actually, Siler is a library, not a framework (maybe a micro-framework), the overall program flow of control is dictated by you. So, no hidden configs or predefined directory structures.

### Hello World

```php
<?php

require 'vendor/autoload.php';

Siler\Route\get('/', function() {
    echo 'Hello World';
});
```
Nothing more, nothing less. You don't need even tell Siler to `run` or something like that.

As said before, Siler aims to use PHP files and functions as first-class citiziens, so no Controllers here. If you want to call something more self-container instead of a Closure, you can simply give a PHP filename then Siler will require it for you.

<sub>index.php</sub>
```php
<?php
require 'vendor/autoload.php';
Siler\Route\get('/', 'pages/home.php');
```

<sub>pages/home.php</sub>
```php
<?php
echo 'Hello World';
```

## Namespaces

Siler doesn't try to be a fully-featured framework - don't even aim to be a framework - instead it embraces component based architectures and offers helper functions to work with this components under PHP namespaces.

### Twig

Is one of the libraries that has helpers functions making work with templates quite simple.

```bash
composer require twig/twig
```

<sub>index.php</sub>
```php
<?php

require 'vendor/autoload.php'

use Siler\{Twig, Route};

Twig\init('/path/to/templates');
Route\get('/', 'pages/home.php');
```

<sub>pages/home.php</sub>
```php
<?php

use Siler\Twig;
use Siler\Http\Response;

Response\html(Twig\render('pages/home.twig'));
```

### Dotenv

Siler also brings helper functions for [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv), so you can easily acomplish [twelve-factor](https://12factor.net/) apps.

```bash
composer require vlucas/phpdotenv
```

<sub>.env</sub>
```dotenv
TWIG_DEBUG=true
```

<sub>index.php</sub>
```php
<?php

require 'vendor/autoload.php'

use Siler\{Dotenv, Twig, Route};

Dotenv\init('/path/to/.env');
Twig\init('/path/to/templates', '/path/to/templates/cache', Dotenv\env('TWIG_DEBUG'));
Route\get('/', 'pages/home.php');
```

### Ratchet

Doing some real-time apps with WebSockets? No problem. Siler simplifies Ratchet.

```bash
composer require cboden/ratchet
```

<sub>index.php</sub>
```php
<?php

require 'vendor/autoload.php';

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

🚧 WIP

---
MIT
