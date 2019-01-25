# Twig Templating

```
composer require twig/twig
```

{% hint style="info" %}
Siler doesn't have direct dependencies, to stay fit, it favors peer dependencies, which means you have to explicitly declare a `twig` dependency in your project in order to use it.
{% endhint %}

Siler will internally handle the `Twig_Environment` instance.

```php
use Siler\Twig;

Twig\init('path/to/templates');
```

Actually it is also returned at `init` function call, so you call add Twig plugins, filters and functions, for example, adding `Siler\Http\url` into Twig's Environment to later reference static assets on the public folder:

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

Something that can be confusing using Siler is that some function does outputs and other doesn't, like `Twig\render`. So remember that `Twig\render` will only return the rendered template within its given data and you should explicit output or let `Response` do it:

```php
$html = Twig\render('pages/home.twig');
Response\html($html);
```

Also, remember that **you can always bring you own template engine to the playground without any bridging stuff** or use PHP itself on your views.

