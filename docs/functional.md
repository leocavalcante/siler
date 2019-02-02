---
description: >-
  Functional programming treats computation as the evaluation of mathematical
  functions and avoids changing-state and mutable data. It is declarative, which
  means expressions instead of statements.
---

# λ Functional

Siler is bundled with the `Siler\Functional` namespace. It brings some function declarations that aids the work with another **first-class** and **high-order** PHP functions!

#### `identity()`

Returns a Closure that returns its given arguments.

```php
use Siler\Functional as λ;

array_map(λ\identity(), [1, 2, 3]);
// [1, 2, 3]
```

{% hint style="info" %}
Doesn't seam useful at first, but working with the functional paradigm, you'll find the reason shortly.
{% endhint %}

#### `always($value)`

Almost like `identity()`, but it always returns the given value.

```php
use Siler\Functional as λ;

array_map(λ\always('foo'), range(1, 3));
// [foo, foo, foo]
```

#### `if_else(callable $cond) -> $then -> $else`

A functional if/then/else.

```php
use Siler\Functional as λ;

$pred = λ\if_else(λ\equal('foo'))(λ\always('is foo'))(λ\always('isnt foo'));

echo $pred('foo'); // is foo
echo $pred('bar'); // isnt foo
```

#### `partial(callable $callable, ...$partial)`

Partial application refers to the process of fixing a number of arguments to a function, producing another function of smaller [arity](https://en.wikipedia.org/wiki/Arity). Given a function![{\displaystyle f\colon \(X\times Y\times Z\)\to N}](https://wikimedia.org/api/rest_v1/media/math/render/svg/5c7acf81877307746cd88e2785967d9a2f287107), we might fix \(or 'bind'\) the first argument, producing a function of type ![{\displaystyle {\text{partial}}\(f\)\colon \(Y\times Z\)\to N}](https://wikimedia.org/api/rest_v1/media/math/render/svg/d45fcfd39c660c562ebd3da8158dbfd8f673836e).  
[https://en.wikipedia.org/wiki/Partial\_application](https://en.wikipedia.org/wiki/Partial_application)

Nothing like a good example:

```php
use Siler\Functional as λ;

$add = function ($a, $b) {
    return $a + $b;
};

$add2 = λ\partial($add, 2);

echo $add2(3); // 5
```

Works with any `callable`:

```php
use Siler\Functional as λ;

$explodeCommas = λ\partial('explode', ',');
print_r($explodeCommas('foo,bar,baz'));

/**
 * Array
 * (
 *  [0] => foo
 *  [1] => bar
 *  [2] => baz
 * )
 */
```

{% hint style="info" %}
There are a lot more of them. A good place it check it out are [the tests](https://github.com/leocavalcante/siler/blob/master/tests/Unit/Functional/FunctionalTest.php).
{% endhint %}

