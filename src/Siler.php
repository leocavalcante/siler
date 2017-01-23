<?php

namespace Siler;

function array_get($array, $key = null, $default = null) {
    if (is_null($key)) {
        return $array;
    }

    return array_key_exists($key, $array) ? $array[$key] : $default;
}

function require_fn($filename) {
    return function ($params = null) use ($filename) {
        return require($filename);
    };
}
