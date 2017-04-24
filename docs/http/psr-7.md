# PSR-7: HTTP message interfaces

Siler supports design decisions provided by the PHP community. In addition to style choices like PSR-2 and consistency like PSR-4, for HTTP abstraction you can use the PSR-7 with the help of [zend-diactoros](https://github.com/zendframework/zend-diactoros).

```bash
$ composer require zendframework/zend-diactoros
```

You can create a _superglobals_ seeded `ServerRequest` with `Siler\Diactoros\request()`:

```php
use Siler\Diactoros;

$request = Diactoros\request();
```

And create Responses through this helpers:

```php
$json = Diactoros\json(['some' => 'data']);
$html = Diactoros\html('<p>some markup</p>');
$text = Diactoros\text('plain text');
```

If none of them fits your needs, you can create a raw Response:

```php
$response = Diactoros\response();
$response->getBody()->write('something');
```

To emit a Response, there is no big deal, if you got Siler, already imagined that is also about a function call:

```php
Diactoros\emit($response);
```

As in `Siler\Http\Response` functions, the `Diactoros\emit` will output headers and text to the buffer, so use carefully.

For full example:

```php
use Siler\Route;
use Siler\Twig;
use Siler\Diactoros;

Twig\init('path/to/templates');

Route\get('/', function () {
    $query = Diactoros\request()->getQueryParams();
    $html = Twig\render('pages/home.twig', compact('query'));
    $response = Diactoros\html($html);
    
    Diactoros\emit($response);
});
```



