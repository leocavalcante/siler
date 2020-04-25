<p align="center">
<!-- ALL-CONTRIBUTORS-BADGE:START - Do not remove or modify this section -->
[![All Contributors](https://img.shields.io/badge/all_contributors-2-orange.svg?style=flat-square)](#contributors-)
<!-- ALL-CONTRIBUTORS-BADGE:END -->
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

## Contributors

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
<!-- prettier-ignore-start -->
<!-- markdownlint-disable -->
<table>
  <tr>
    <td align="center"><a href="https://leocavalcante.dev"><img src="https://avatars3.githubusercontent.com/u/183722?v=4" width="100px;" alt=""/><br /><sub><b>Leo Cavalcante</b></sub></a><br /><a href="#maintenance-leocavalcante" title="Maintenance">🚧</a></td>
    <td align="center"><a href="http://shoggoth.net"><img src="https://avatars3.githubusercontent.com/u/1096923?v=4" width="100px;" alt=""/><br /><sub><b>Matt Wiseman</b></sub></a><br /><a href="https://github.com/leocavalcante/siler/commits?author=trollboy" title="Code">💻</a></td>
  </tr>
</table>

<!-- markdownlint-enable -->
<!-- prettier-ignore-end -->
<!-- ALL-CONTRIBUTORS-LIST:END -->
<!-- ALL-CONTRIBUTORS-LIST:END -->

## License

[![License](http://img.shields.io/:License-MIT-blue.svg?style=flat-square)](https://github.com/leocavalcante/siler/blob/master/LICENSE)

- **[MIT license](http://opensource.org/licenses/mit-license.php)**
- Copyright 2020 © <a href="https://leocavalcante.dev" target="_blank">LC</a>
