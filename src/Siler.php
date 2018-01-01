<?php
/**
 * Siler core file.
 */

namespace Siler;

/**
 * Get a value from an array checking if the key exists and returning a default value if not.
 *
 * @param array  $array   The array to be searched on
 * @param string $key     The key to be searched
 * @param mixed  $default The default value to be returned when the key don't exists
 *
 * @return mixed
 */
function array_get($array, $key = null, $default = null)
{
    if (is_null($key)) {
        return $array;
    }

    return array_key_exists($key, $array) ? $array[$key] : $default;
}

/**
 * Returns a function that requires the given filename.
 *
 * @param string $filename The file to be required
 *
 * @return \Closure
 *
 * @psalm-suppress UnresolvableInclude
 */
function require_fn($filename)
{
    return function ($params = null) use ($filename) {
        return require $filename;
    };
}
