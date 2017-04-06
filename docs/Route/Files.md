# Route to files

You can also let Siler create the routes recursively looking for files at a base path. Then the files names will be used to define the method and the path.

```php
use Siler\Route;

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
