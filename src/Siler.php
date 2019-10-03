<?php

declare(strict_types=1);

/*
 * Siler core file.
 */

namespace Siler;

use Closure;

/**
 * Get a value from an array checking if the key exists and returning a default value if not.
 *
 * @param array|null $array
 * @param mixed $key The key to be searched
 * @param mixed $default The default value to be returned when the key don't exists
 * @param bool $caseInsensitive Ignore key case, default false
 *
 * @return mixed
 */
function array_get(?array $array, $key = null, $default = null, bool $caseInsensitive = false)
{
    if (is_null($array)) {
        return $default;
    }

    if (is_null($key)) {
        return $array;
    }

    if ($caseInsensitive) {
        $array = array_change_key_case($array);
        $key = strtolower($key);
    }

    return array_key_exists($key, $array) ? $array[$key] : $default;
}

/**
 * Returns a function that requires the given filename.
 *
 * @param string $filename The file to be required
 *
 * @return Closure
 */
function require_fn(string $filename): Closure
{
    return function (array $params = []) use ($filename) {
        if (!file_exists($filename)) {
            return null;
        }

        if (!Container\has($filename)) {
            /** @noinspection PhpIncludeInspection */
            Container\set($filename, include_once $filename);
        }

        $value = Container\get($filename);

        if (is_callable($value)) {
            return call_user_func($value, $params);
        }

        return $value;
    };
}
