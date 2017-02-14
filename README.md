# Siler
PHP files and functions as first-class citizens.

[![Build Status](https://travis-ci.org/leocavalcante/siler.svg?branch=master)](https://travis-ci.org/leocavalcante/siler)
[![codecov](https://codecov.io/gh/leocavalcante/siler/branch/master/graph/badge.svg)](https://codecov.io/gh/leocavalcante/siler)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/leocavalcante/siler/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/leocavalcante/siler/?branch=master)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)
[![Latest Unstable Version](https://poser.pugx.org/leocavalcante/siler/v/unstable)](//packagist.org/packages/leocavalcante/siler)
[![License](https://poser.pugx.org/leocavalcante/siler/license)](https://packagist.org/packages/leocavalcante/siler)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/703f233e-0738-4bf3-9d47-09d3c6de19b0/mini.png)](https://insight.sensiolabs.com/projects/703f233e-0738-4bf3-9d47-09d3c6de19b0)

Zero dependecy. Everything should be built on top of PHP's built-in functions and helpers around vendors are totally optional.

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
---
Since it's plain old PHP files and functions, no surprises it's **blazing fast!**
![Benchmark](benchmark.png)
*Benchmark powered by: [github.com/kenjis/php-framework-benchmark](https://github.com/kenjis/php-framework-benchmark) [Official results](https://github.com/kenjis/php-framework-benchmark/pull/74#issuecomment-279357554)

[Project example](https://github.com/leocavalcante/siler-example)
