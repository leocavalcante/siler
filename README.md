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
    <a href="https://insight.sensiolabs.com/projects/703f233e-0738-4bf3-9d47-09d3c6de19b0" target="_blank">
        <img src="https://insight.sensiolabs.com/projects/703f233e-0738-4bf3-9d47-09d3c6de19b0/mini.png">
    </a>
</p>

Keep it simple, *stupid*!

Siler core principles includes:
* PHP files and functions as first-class citizens
* Zero dependecy. Everything should be built on top of PHP's built-in functions and helpers around vendors are totally optional

###### index.php
```php
<?php
require_once __DIR__.'/../vendor/autoload.php';
use Siler\Route;
Route\get('/', 'pages/home.php');
```
###### pages/home.php
```php
<?php echo 'Hello World';
```

Since it's plain old PHP files and functions, no surprises it's **blazing fast!**
![Benchmark](benchmark.png)
Benchmark powered by: [github.com/kenjis/php-framework-benchmark](https://github.com/kenjis/php-framework-benchmark)

[API documentation](https://leocavalcante.github.io/siler/namespaces/Siler.html)<br>
[Project example](https://github.com/leocavalcante/siler-example)
