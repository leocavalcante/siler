<p align="center">
    <br><br><br><br>    
    <img src="https://raw.githubusercontent.com/leocavalcante/siler/master/si%CE%BBer.png" height="100"/>
    <br><br><br><br>
</p>

<!-- Place this tag where you want the button to render. -->
<a class="github-button" href="https://github.com/leocavalcante/siler" data-count-href="/leocavalcante/siler/stargazers" data-count-api="/repos/leocavalcante/siler#stargazers_count" data-count-aria-label="# stargazers on GitHub" aria-label="Star leocavalcante/siler on GitHub">Star</a>
<!-- Place this tag in your head or just before your close body tag. -->
<script async defer src="https://buttons.github.io/buttons.js"></script>

Siler is a set of general purpose high-level abstractions aiming an API for declarative programming in PHP.

> Simplicity is the ultimate sophistication. – <cite>Leonardo Da Vinci</cite>

You can use it within your current framework or standalone, as some kind of micro-framework:

```shell
$ composer require leocavalcante/siler dev-master
```

## Hello World

```php
use Siler\Functional as λ;
use Siler\Route;

Route\get('/', λ\puts('Hello World'));
```

This outputs `Hello World` when the file is reached via HTTP using the GET method and the URI path matches `/`. Got the idea, right?

See here on the left a section to read more about.

Feel free to submit any [issues](https://github.com/leocavalcante/siler/issues) or [pull requests](https://github.com/leocavalcante/siler/pulls).
