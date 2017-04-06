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

This is outputs `Hello World` when the file is reached via HTTP using the GET method and the URI path matches `/`. Got the ideia, right?

What can be done with Siler?

* [Route](Route/README.md)
  * [Callable](Route/Callable.md)
  * [Filename](Route/Filename.md)
  * [Resource](Route/Resource.md)
  * [Files](Route/Files.md)
* [Http](Http.md)
