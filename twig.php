<?php

if (!defined('TWIG_TEMPLATES')) {
    throw new Exception('TWIG_TEMPLATES not defined');
}

if (!defined('TWIG_TEMPLATES_CACHE')) {
    throw new Exception('TWIG_TEMPLATES_CACHE not define');
}

$twig = new \Twig_Environment(new \Twig_Loader_Filesystem(TWIG_TEMPLATES), [
    'debug' => defined('TWIG_DEBUG') ? TWIG_DEBUG : false,
    'cache' => TWIG_TEMPLATES_CACHE,
]);

if (function_exists('url')) {
    $twig->addFunction(new \Twig_SimpleFunction('url', 'url'));
}

function render(string $name, array $data = []) {
    global $twig;
    return $twig->render($name, $data);
}
