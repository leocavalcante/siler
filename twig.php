<?php

if (!defined('TWIG_TEMPLATES')) {
    die('TWIG_TEMPLATES not defined');
}

if (!defined('TWIG_TEMPLATES_CACHE')) {
    die('TWIG_TEMPLATES_CACHE not define');
}

$twig = new \Twig_Environment(new \Twig_Loader_Filesystem(TWIG_TEMPLATES), [
    'debug' => env('APP_DEBUG'),
    'cache' => TWIG_TEMPLATES_CACHE,
]);

if (function_exists('url')) {
    $twig->addFunction(new \Twig_SimpleFunction('url', 'url'));
}

function render(string $name, array $data = []) {
    global $twig;
    return $twig->render($name, $data);
}
