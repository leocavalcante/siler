# Route to `callable`

Routes can be set to a PHP `callable`

As a anonymous function:

```php
use Siler\Route;

Route\get('/', function () {
  echo 'Hello Closure';
});
```

As a method call using PHP's array notation:

```php
use Siler\Route;

class Hello {
  public function method() {
    echo 'Hello Array Notation';
  }
}

$obj = new Hello();
Route\post('/', [$obj, 'method']);
```

As a `__invoke` method on classes:

```php
use Siler\Route;

class Hello {
  public function __invoke() {
    echo 'Hello Invoke';
  }
}

Route\put('/', new Hello());
```

Anyway, by any kind of [PHP Callable](http://php.net/manual/en/language.types.callable.php).
