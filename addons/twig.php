<?php

$twigEnv = null;

function create_twig_env($templatesPath, $templatesCachePath = null, $debug = null) {
    global $twigEnv;

    if (is_null($debug)) {
        $debug = false;
    }

    $twigEnv = new \Twig_Environment(new \Twig_Loader_Filesystem($templatesPath), [
        'debug' => $debug,
        'cache' => $templatesCachePath,
    ]);

    if (function_exists('url')) {
        $twigEnv->addFunction(new \Twig_SimpleFunction('url', 'url'));
    }

    return $twigEnv;
}

function render($name, $data = []) {
    global $twigEnv;
    return $twigEnv->render($name, $data);
}
