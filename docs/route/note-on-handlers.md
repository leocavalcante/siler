# Note on Handlers

When creating routes, becareful about **early** and **lazy** evaluations.

For example:

```php
Route\get('/foo', [new FooController(), 'index']);
```

Is **early**, which means it will call `FooController` constructor for each request even if it's not a request to `/foo`.<br>
To make it **lazy** you can wrap inside a `Closure`:

```php
Route\get('/foo', function () {
  $controller = new FooController();
  return $controller->index();
});
```

Now `FooController` is called only when there is a match on route `/foo`.<br>
One downside is that now you have to explictly manage path parameters, on the other hand is a best practice to do so.<br>
It is a good time to validate parameters, convert plain string params to meaninful types on your domain or resolve dependencies.

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
