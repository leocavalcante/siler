# Route to a file

If a string is given, it will be treated as a filename and will automatically be required and the path matches the route.

###### index.php
```php
use Siler\Route;

Route\get('/', 'pages/home.php');
```

###### pages/home.php
```php
use Siler\Http\Response;

Response\html('<p>Hello World</p>');
```
