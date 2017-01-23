# Siler

<p>
    <a href="https://travis-ci.org/leocavalcante/siler" target="_blank">
        <img src="https://img.shields.io/travis/leocavalcante/siler/master.svg?style=flat-square">
    </a>
    <a href="https://codecov.io/github/leocavalcante/siler" target="_blank">
        <img src="https://img.shields.io/codecov/c/github/leocavalcante/siler.svg?style=flat-square">
    </a>
    <a href="https://scrutinizer-ci.com/g/leocavalcante/siler/" target="_blank">
        <img src="https://img.shields.io/scrutinizer/g/leocavalcante/siler.svg?style=flat-square">
    </a>
</p>

Keep it simple, *stupid*!

###### index.php
```php
<?php

use function Siler\Route\route;

require_once __DIR__.'/../vendor/autoload.php';

route('/', 'pages/home.php');

// route paths (first argument) will be wrapped inside: #^{$path}/?$#
// you can use regexp to catch url params, like: /foo/([a-z]+)
// or you can use Siler's placeholder (aka named regexp groups): /foo/{bar}
// @see the tests!
```

###### pages/home.php
```php
<?php

// middlewares...

// middlewares...

// middlewares...

/*
whatever middlewares you want
this is just a file, an entry point
you dont need to unit test it
so you dont need to invert its control, no need for dependency injection

instantiate and use your well-tested middlewares and services!
*/

$message = 'It works';

// you can use your favorite template engine
echo Siler\Twig\render('home.twig', compact('message'));
// ^ Siler comes with a helper for Twig
?>
<h1><?= $message ?></h1>
<p>or just output phtml here!</p>
```

Doesn't get it?
[Check out this example](https://github.com/leocavalcante/siler-example)
