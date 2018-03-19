# Http

Siler has some features to work with common HTTP.

## Request

### Body

You can grab the raw request body using:

```php
use Siler\Http\Request;

$body = Request\raw();
```

Parse as URL-encoded data using:

```php
$params = Request\params();
```

Parse as JSON using:

```php
$resource = Request\json();
```

### $\_GET and $\_POST superglobals

Or get data from a Form and from the Query String using:

```php
$input = Request\post('input');
$searchTerm = Request\get('q');
```

Calling them without arguments will tell Siler to return all the values as an `array`:

```php
$data = Request\post();
$queryString = Request\get();
```

You can also pass a default value if the key isn't present in the GET or POST super-globals:

```php
$input = Request\post('input', 'default-value');
```

### Headers

Also conveniently get a header as easy as for the body:

```php
$contentType = Request\header('Content-Type');
```

_e.g. Serving both JSON and Form requests:_

```php
$data = Request\header('Content-Type') == 'json' ? Request\json() : Request\post();
```

---

## Response

Siler also have convenient functions to simplify HTTP responses.

You can output JSON encoded body with proper headers in just one line:

```php
use Siler\Http\Response;

Response\json(['error' => false, 'message' => 'It works']);
```

It will already output the given data, you don't need to call `echo` or `print` so use carefully, it's not a encoder, it's an output-er.

### Headers

Same easy as for Request, you can set HTTP response headers with the `header` function at `Response` namespace:

```php
Response\header('Access-Control-Allow-Origin', 'https://know-domain.tld');
Response\header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE');
```
