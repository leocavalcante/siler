# Route

Declare path listeners for a web app.

Siler comes with 5 convenient helpers for common HTTP methods: GET, POST, PUT, DELETE and OPTIONS.

```php
use Siler\Route;

Route\get('/path', <handler>);
Route\post('/path', <handler>);
Route\put('/path', <handler>);
Route\delete('/path', <handler>);
Route\options('/path', <handler>);
```

Additional and custom methods can be set using the `route` function:

```php
Route\route('patch', '/path', <handler>');
```

As well as multiple methods using an `array`:

```php
Route\route(['post', 'put'], '/path', <handler>);
```

Parameters can be set using regular expressions:

```php
Route\get('/number/([0-9]+)', <handler>);
```

Or the convenient regexifier that will automatically create named groups using brackets `{}` as delimiters:

```php
Route\get('/number/{n}', <handler>);
```

Please, note that named groups will match any URL-safe text that is given between slashes. If you need more accurate matching use regular expressions.

The URL matches are available to the handlers by the `$params` pseudo-global.

## Handlers

Route handlers can be set using:

* [Callable](callable.md)
* [Filename](filename.md)
* [Resource](resource.md)
* [Files](files.md)
