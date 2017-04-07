# Twig Templating

Siler doesn't have its own template engine, instead it let you bring your own. But, since [Twig ](http://twig.sensiolabs.org/)is pretty popular among PHP developers, Siler has a tiny helper to handle Twig initialization and rendering.

```php
use Siler\Twig;

Twig\init('path/to/templates');
```

A pitfall - that actually is a advantage - is that Siler's helpers dependencies needs explicit declaration, which means that you have to require Twig by yourself in order to use Siler's helpers.

```bash
$ composer require twig/twig
```

Siler will internally handle the `Twig_Environment` instance. Actually it is also returned at `init` function call, so you call add Twig plugins, filters and functions, for example, adding `Siler\Http\url` into Twig's Environment to later reference static assets on the public folder:

```php
Twig\init('path/to/templates')
    ->addFunction(new Twig_SimpleFunction('url', 'Siler\Http\url'));
```

At initialization, you can also provide a path to templates cache as second argument and if you want to let Twig debug as third argument \(defaults to `false`\):

```php
$shouldTwigDebug = true;
Twig\init('path/to/templates', 'path/to/templates/cache', $shouldTwigDebug);
```

To render a template, simply call `render` at Twig namespace:

```php
echo Twig\render('pages/home.twig');
```

An passing parameters can be done by the second argument:

```php
$data = ['message' => 'Hello World'];
echo Twig\render('pages/home.twig', $data);
```

Something that can be confusing using Siler is that some function does outputs and other doesn't, like `Twig\render`. So remember that `Twig\render` will only return the rendered template within its given data and you should explicit output or let `Response `do it:

```php
$html = Twig\render('pages/home.twig');
Response\html($html);
```

Also, remember that **you can always bring you own template engine to the playground without any bridging stuff** or use PHP itself on your views.
