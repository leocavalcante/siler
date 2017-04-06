# Route to a resource

CRUD routes can be [auto-magically](https://github.com/leocavalcante/siler/blob/master/src/Route/Route.php#L109) be defined for convenience using the Rails and Laravel pattern.

Given this `resource` declaration:

```php
use Siler\Route;

Route\resource('/users', 'crud/users');
```

Siler will look for files at `path/to/files` matching the HTTP URI according to the table below:

| HTTP Verb | URI | File |
| :--- | :--- | :--- |
| GET | `/users` | index.php |
| GET | `/users/create` | create.php |
| POST | `/users` | store.php |
| GET | `/users/{id}` | show.php |
| GET | `/users/{id}/edit` | edit.php |
| PUT | `/users/{id}` | update.php |
| DELETE | `/users/{id}` | destroy.php |

And the file structure:

```
index.php
/crud
+ /users
++ index.php
++ create.php
++ store.php
++ show.php
++ edit.php
++ update.php
++ destroy.php
```
