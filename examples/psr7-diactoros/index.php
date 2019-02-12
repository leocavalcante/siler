<?php

declare(strict_types=1);

chdir(dirname(dirname(__DIR__)));
require_once 'vendor/autoload.php';

use function Siler\Diactoros\html;
use function Siler\Diactoros\json;
use function Siler\Diactoros\request;
use function Siler\Diactoros\text;
use function Siler\HttpHandlerRunner\sapi_emit;
use function Siler\Route\get;
use function Siler\Twig\init as twig;
use function Siler\Twig\render;

// call like: /to-json?foo=bar
get(
    '/to-json',
    function () {
        // output: {"foo":"bar"}
        sapi_emit(json(request()->getQueryParams()));
    }
);

twig('examples/psr7-diactoros');

// call like: /to-html?foo=bar
get(
    '/to-html',
    function () {
        // outputs a table with foo bar, see: template.twig
        sapi_emit(html(render('template.twig', ['query' => request()->getQueryParams()])));
    }
);

// call like: /to-text?foo=bar
get(
    '/to-text',
    function () {
        // output: foo=bar
        sapi_emit(text(http_build_query(request()->getQueryParams())));
    }
);
