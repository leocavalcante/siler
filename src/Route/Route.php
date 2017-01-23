<?php

namespace Siler\Route;

use function Siler\Http\path;
use function Siler\require_fn;

function route($path, $callback)
{
    $path = preg_replace('/\{([A-z]+)\}/', '(?<$1>.*)', $path);
    $path = "#^{$path}/?$#";

    if (is_string($callback)) {
        $callback = require_fn($callback);
    }

    if (preg_match($path, path(), $params)) {
        $callback($params);
    }
}
