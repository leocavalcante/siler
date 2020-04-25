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

## Getting started

### Installation

```bash
$ composer require leocavalcante/siler
```

That is it. Actually, Siler is a library, not a framework (maybe a micro-framework), the overall program flow of control is dictated by you. So, no hidden configs or predefined directory structures.

### Hello, World!

```php
use function Siler\{Functional\puts, Route\get};

get('/', puts('hello world'));
```
Nothing more, nothing less. You don't need even tell Siler to `run` or something like that (`puts` works like a lazily evaluated `echo`).

## What is next?

- [Documentation](https://siler.leocavalcante.dev/)
- [Examples](https://github.com/siler-examples)

---

## License

[![License](http://img.shields.io/:License-MIT-blue.svg?style=flat-square)](https://github.com/leocavalcante/siler/blob/master/LICENSE)

- **[MIT license](http://opensource.org/licenses/mit-license.php)**
- Copyright 2020 © <a href="https://leocavalcante.dev" target="_blank">LC</a>
