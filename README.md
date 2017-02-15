# Siler
PHP files and functions as first-class citizens.

[![Build Status](https://travis-ci.org/leocavalcante/siler.svg?branch=master)](https://travis-ci.org/leocavalcante/siler)
[![codecov](https://codecov.io/gh/leocavalcante/siler/branch/master/graph/badge.svg)](https://codecov.io/gh/leocavalcante/siler)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/leocavalcante/siler/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/leocavalcante/siler/?branch=master)
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


```php
<?php

require 'vendor/autoload.php';
Siler\Route\get('/', 'pages/home.php');
```
<sub>index.php</sub>

```php
<?php
echo 'Hello World';
```
<sub>pages/home.php</sub>

---
MIT
