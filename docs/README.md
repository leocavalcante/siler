# Siler

Siler is a set of general purpose high-level abstractions aiming an API for declarative programming in PHP.

> Simplicity is the ultimate sophistication. – <cite>Leonardo Da Vinci</cite>

You can use it within your current framework or standalone, as some kind of micro-framework:

```shell
$ composer require leocavalcante/siler dev-master
```

## Hello World

```php
use Siler\Functional as λ;
use Siler\Route;

Route\get('/', λ\puts('Hello World'));
```

This outputs `Hello World` when the file is reached via HTTP using the GET method and the URI path matches `/`. Got the idea, right?

See here on the left a section to read more about.

Feel free to submit any [issues](https://github.com/leocavalcante/siler/issues) or [pull requests](https://github.com/leocavalcante/siler/pulls).
