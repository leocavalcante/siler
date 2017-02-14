# Siler
PHP files and functions as first-class citizens.

[![Build Status](https://travis-ci.org/leocavalcante/siler.svg?branch=master)](https://travis-ci.org/leocavalcante/siler)
[![codecov](https://codecov.io/gh/leocavalcante/siler/branch/master/graph/badge.svg)](https://codecov.io/gh/leocavalcante/siler)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/leocavalcante/siler/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/leocavalcante/siler/?branch=master)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)
[![Latest Unstable Version](https://poser.pugx.org/leocavalcante/siler/v/unstable)](//packagist.org/packages/leocavalcante/siler)
[![License](https://poser.pugx.org/leocavalcante/siler/license)](https://packagist.org/packages/leocavalcante/siler)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/703f233e-0738-4bf3-9d47-09d3c6de19b0/mini.png)](https://insight.sensiolabs.com/projects/703f233e-0738-4bf3-9d47-09d3c6de19b0)

Zero dependecy. Everything should be built on top of PHP's built-in functions and helpers around vendors are totally optional.<br>
[Project example](https://github.com/leocavalcante/siler-example)

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

<table>
<thead>
<tr>
<th>framework</th>
<th align="right">requests per second</th>
<th align="right">relative</th>
<th align="right">peak memory</th>
<th align="right">relative</th>
</tr>
</thead>
<tbody>
<tr>
<td>siler-0.6</td>
<td align="right">2,488.82</td>
<td align="right">22.3</td>
<td align="right">0.24</td>
<td align="right">1.0</td>
</tr>
<tr>
<td>phalcon-2.0</td>
<td align="right">1,773.62</td>
<td align="right">15.9</td>
<td align="right">0.26</td>
<td align="right">1.1</td>
</tr>
<tr>
<td>tipsy-0.10</td>
<td align="right">1,398.06</td>
<td align="right">12.5</td>
<td align="right">0.32</td>
<td align="right">1.3</td>
</tr>
<tr>
<td>fatfree-3.5</td>
<td align="right">1,005.67</td>
<td align="right">9.0</td>
<td align="right">0.41</td>
<td align="right">1.7</td>
</tr>
<tr>
<td>ci-3.0</td>
<td align="right">812.32</td>
<td align="right">7.3</td>
<td align="right">0.42</td>
<td align="right">1.7</td>
</tr>
<tr>
<td>laravel-5.3</td>
<td align="right">111.82</td>
<td align="right">1.0</td>
<td align="right">2.51</td>
<td align="right">10.4</td>
</tr></tbody>
<tfooter>
<tr><td colspan="5" align="right"><em><a href="https://github.com/kenjis/php-framework-benchmark/pull/74#issuecomment-279357554" target="_blank">benchmark source</a></em></td></tr>
</tfooter>
</table>

&copy; 2017 MIT
