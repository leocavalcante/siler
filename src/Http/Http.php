<?php
/**
 * Helpers for the HTTP abstraction.
 */

namespace Siler\Http;

use function Siler\array_get;

/**
 * Get a value from the $_COOKIE global.
 *
 * @param string $key     The key to be searched
 * @param mixed  $default The default value to be returned when the key don't exists
 *
 * @return mixed
 */
function cookie($key = null, $default = null)
{
    return array_get($_COOKIE, $key, $default);
}

/**
 * Get a value from the $_SESSION global.
 *
 * @param string $key     The key to be searched
 * @param mixed  $default The default value to be returned when the key don't exists
 *
 * @return mixed
 */
function session($key = null, $default = null)
{
    return array_get($_SESSION, $key, $default);
}

/**
 * Set a value in the $_SESSION global.
 *
 * @param string $key   The key to be used
 * @param mixed  $value The value to be stored
 */
function setsession($key, $value)
{
    $_SESSION[$key] = $value;
}

/**
 * Get a value from the $_SESSION global and remove it.
 *
 * @param string $key     The key to be searched
 * @param mixed  $default The default value to be returned when the key don't exists
 *
 * @return mixed
 *
 * @psalm-suppress PossiblyNullArrayOffset
 */
function flash($key = null, $default = null)
{
    $value = session($key, $default);
    unset($_SESSION[$key]);

    return $value;
}

/**
 * Redirects using the HTTP Location header.
 *
 * @param string $url The url to be redirected to
 */
function redirect($url)
{
    Response\header('Location', $url);
}

/**
 * Returns a path based on the projects base url.
 *
 * @param string $path Concat some URI
 *
 * @return string
 */
function url($path = null)
{
    if (is_null($path)) {
        $path = '/';
    }

    $scriptName = array_get($_SERVER, 'SCRIPT_NAME', '');

    return rtrim(str_replace('\\', '/', dirname($scriptName)), '/').'/'.ltrim($path, '/');
}

/**
 * Get the current HTTP path info.
 *
 * @return string
 */
function path()
{
    $scriptName = array_get($_SERVER, 'SCRIPT_NAME', '');
    $queryString = array_get($_SERVER, 'QUERY_STRING', '');
    $requestUri = array_get($_SERVER, 'REQUEST_URI', '');

    $requestUri = str_replace('?'.$queryString, '', $requestUri);
    $scriptPath = str_replace('\\', '/', dirname($scriptName));

    if (!strlen(str_replace('/', '', $scriptPath))) {
        return '/'.ltrim($requestUri, '/');
    } else {
        return '/'.ltrim(str_replace($scriptPath, '', $requestUri), '/');
    }
}

/**
 * Get the absolute project's URI.
 *
 * @param string $protocol Pass a protocol, defaults to http or https
 *
 * @return string
 */
function uri($protocol = null)
{
    $https = array_get($_SERVER, 'HTTPS', '');

    if (is_null($protocol)) {
        $protocol = empty($https) ? 'http' : 'https';
    }

    $httpHost = array_get($_SERVER, 'HTTP_HOST', '');

    return $protocol.'://'.$httpHost.path();
}
