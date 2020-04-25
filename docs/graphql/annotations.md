---
description: >-
  On the previous guide you saw how to map resolvers (callables) from a existing
  SDL (.graphql or .gql). Annotations enables the other way around, it provides
  a GraphQL SDL from annotated PHP code.
---

# @Annotations

## Thank you Doctrine

Siler's GraphQL Annotations uses the super-powers from Doctrine's Annotations and like any other dependency, is a peer that we should explicitly require:

```
$ composer require doctrine/annotations
```

## What is available:

There are 9 annotations fulfilling the GraphQL's ecosystem:

Class annotations are:

* ObjectType
* InterfaceType
* InputType
* EnumType
* UnionType
* Directive

Complementary method and property annotations are:

* Field
* Args
* EnumVal

They follow a ubiquitous language to GraphQL spec, so if you know GraphQL, there is nothing new here, you probably already know what each of them does just by its name.

## Hello, World!

Let's start by defining our root query:

```php
<?php declare(strict_types=1);

namespace App;

use GraphQL\Type\Definition\ResolveInfo;
use Siler\GraphQL\Annotation\Field;
use Siler\GraphQL\Annotation\ObjectType;

/** @ObjectType */
class Query
{
    /** @Field(description="A common greet") */
    public static function hello(): string
    {
        return 'Hello, World!';
    }
}
```

The `ObjectType` name will be inferred by the class name, so it will already be _Query_.

Then we just provide this class to the `annotated` function on the `Siler\GraphQL` namespace:

```php
<?php declare(strict_types=1);

namespace App;

use function Siler\GraphQL\{annotated, init};

require_once __DIR__ . '/vendor/autoload.php';

$schema = annotated([Query::class]);
init($schema);
```

**And that is it!** It auto-magically servers the following SDL:

```graphql
type Query {
  """
  A common greet
  """
  hello: String!
}
```

With the static `hello` method body already playing the **resolver** role, so:

```graphql
query {
  hello
}
```

Returns:

```javascript
{
  "data": {
    "hello": "Hello, World!"
  }
}
```

**For a full-featured example, please take a look at:** [**github.com/leocavalcante/siler/examples/graphql-annotations**](https://github.com/leocavalcante/siler/tree/master/examples/graphql-annotations)\*\*\*\*

## Caching

Parsing docblocks can be expensive, on production environments is recommended to cache this process by using a caching reader.

First, install `doctrine/cache`:

```javascript
composer require doctrine/cache
```

Then pass a `Doctrine\Common\Cache\Cache` to `Siler\GraphQL\Deannotator::cache()` like:

```javascript
Siler\GraphQL\Deannotator::cache(new ApcuCache());
```

{% hint style="info" %}
Make sure you do this before the `annotated()` call.
{% endhint %}

