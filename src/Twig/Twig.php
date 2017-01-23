<?php

namespace Siler\Twig;

$_twigEnv = null;

function init($templatesPath, $templatesCachePath = null, $debug = null)
{
    global $_twigEnv;

    if (is_null($debug)) {
        $debug = false;
    }

    $_twigEnv = new \Twig_Environment(new \Twig_Loader_Filesystem($templatesPath), [
        'debug' => $debug,
        'cache' => $templatesCachePath,
    ]);

    if (function_exists('url')) {
        $_twigEnv->addFunction(new \Twig_SimpleFunction('url', 'url'));
    }

    return $_twigEnv;
}

function render($name, $data = [])
{
    global $_twigEnv;
    return $_twigEnv->render($name, $data);
}
