---
description: >-
  Siler comes with 5 convenient helpers for common HTTP methods: GET, POST, PUT,
  DELETE and OPTIONS.
---

# Routing

```php
use Siler\Route;

Route\get('/path', <handler>);
Route\post('/path', <handler>);
Route\put('/path', <handler>);
Route\delete('/path', <handler>);
Route\options('/path', <handler>);
```

Also a facade to catch **any HTTP method:**

```php
Route\any('/path', <handler>);
```

Additional and **custom HTTP methods** can be set using the `route` function:

```php
Route\route('custom', '/path', <handler>);
```

With an `array`, you can listen for **multiple HTTP methods** on the same handler:

```php
Route\route(['post', 'put'], '/path', <handler>);
```

### **Route parameters**

Route parameters can be defined using Regular Expressions:

```php
Route\get('/number/([0-9]+)', <handler>);
```

Or using a little syntax for creating named groups, that is just wrapping the parameter name around curly-brackets:

```php
Route\get('/number/{n}', <handler>);
```

{% hint style="info" %}
The above will match anything, not only numbers. For a fine-grained control, please use Regular Expressions.
{% endhint %}

#### Optional parameters

Optional named parameters can be defined using the question mark `?` as sufix:

```php
Route\get('/hello/{name?}', <handler>);
```

### Route handlers

The `<handler>` placeholder you saw is where you can put the route logic and it can be contained on the following:

#### Callables

As [Anonymous functions](http://php.net/manual/en/functions.anonymous.php):

```php
Route\get('/hello/{name}', function (array $routeParams) {
    echo 'Hello '.($routeParams['name'] ?? 'World');
});
```

As a [Closures](http://php.net/manual/en/class.closure.php):

```php
$handler = function (array $routeParams) {
    echo 'Hello World';  
};

function create_handler() {
    return function (array $routeParams) {
        echo 'Hello World';
    };
}

Route\get('/', $handler);
Route\get('/', create_handler());
```

As a method call on array-syntax:

```php
class Hello {
    public function world(array $routeParams) {
        echo 'Hello World';
    }
}

$hello = new Hello();
Route\get('/', [$hello, 'world']);
```

As a static method call string-syntax:

```php
class Hello {
    static public function world(array $routeParams) {
        echo 'Hello World';
    }
}

Route\get('/', 'Hello::world');
```

As any kind of [callable](http://php.net/manual/en/language.types.callable.php):

```php
class Hello {
    public function __invoke(array $routeParams) {
        echo 'Hello World';
    }
}

Route\get('/', new Hello());
```

#### Filenames

Handlers can be a String representing the filename of another PHP file, route parameters will be available at the global `$params` variable:

{% code-tabs %}
{% code-tabs-item title="index.php" %}
```php
Route\get('/hello/{name}', 'pages/home.php');
```
{% endcode-tabs-item %}
{% endcode-tabs %}

{% code-tabs %}
{% code-tabs-item title="pages/home.php" %}
```php
echo 'Hello '.$params['name'];
```
{% endcode-tabs-item %}
{% endcode-tabs %}

#### Resources

CRUD routes can be auto-magically be defined for convenience using the Rails and Laravel pattern.

Given this resource declaration:

```php
Route\resource('/users', 'api/users');
```

Siler will look for files at `path/to/files` matching the HTTP URI according to the table below:

| HTTP Verb | URI | File |
| :--- | :--- | :--- |
| GET | `/users` | `/api/users/index.php` |
| GET | `/users/create` | `/api/users/create.php` |
| POST | `/users` | `/api/users/store.php` |
| GET | `/users/{id}` | `/api/users/show.php` |
| GET | `/users/{id}/edit` | `/api/users/edit.php` |
| PUT | `/users/{id}` | `/api/users/update.php` |
| DELETE | `/users/{id}` | `/api/users/destroy.php` |

The file structure should look like:

```text
index.php
/api
└── /users
    ├─ index.php
    ├─ create.php
    ├─ store.php
    ├─ show.php
    ├─ edit.php
    ├─ update.php
    └─ destroy.php
```

#### Files

You can also let Siler create the routes recursively looking for files at a base path. Then the files names will be used to define the method and the path.

```php
Route\files('controllers');
```

Siler will interpret periods \(.\) as slashes and also maintain folder structure at HTTP path:

| Filename | Method | Path |
| :--- | :--- | :--- |
| `index.get.php` | GET | `/` |
| `index.post.php` | POST | `/` |
| `foo.get.php` | GET | `/foo` |
| `bar/index.get.php` | GET | `/bar` |
| `foo.bar.get.php` | GET | `/foo/bar` |
| `foo/bar.get.php` | GET | `/foo/bar` |
| `foo/bar/index.get.php` | GET | `/foo/bar` |

Since `{` and `}` are valid chars in a filename, route parameters should work as well, but you can define required parameters prefixing with `$` and optional parameters using `@`:

| Filename | Method | Path |
| :--- | :--- | :--- |
| `foo.{slug}.get.php` | GET | `/foo/{slug}` |
| `foo.$slug.get.php` | GET | `/foo/{slug}` |
| `foo.@slug.get.php` | GET | `/foo/{slug?}` |

Any method is valid, it is guessed based on the penultimate "token":

| Filename | Method | Path |
| :--- | :--- | :--- |
| `foo.options.php` | OPTIONS | `/foo` |
| `foo.x-custom.php` | X-CUSTOM | `/foo` |

{% hint style="info" %}
Note on handlers
{% endhint %}

When creating routes, be careful about **early** and **lazy** evaluations.

```php
Route\get('/foo', [new FooController(), 'index']);
```

The example above is **early**, which means it will call `FooController` constructor for each request even if it's not a request to `/foo`.

To make it **lazy** you can wrap inside a `Closure`:

```php
Route\get('/foo', function () {
  $controller = new FooController();
  return $controller->index();
});
```

Now `FooController` is called only when there is a match for route `/foo`.  
One downside is that now you have to explicitly manage path parameters, on the other hand is a best practice to do so.  
It is a good time to validate parameters, convert plain string parameters to meaningful types on your domain or resolve dependencies.

```php
Route\get('/users/{id}', function (array $params) use ($ioc) {  
  if (!preg_match('/[0-9]+/', $params['id']) {
    $controller = $ioc->resolve(ErrorController::class);
    return $controller->invalid('IDs must be numbers');
  }
  
  $controller = $ioc->resolve(UsersController::class);
  return $controller->show($params['id']);
});
```

