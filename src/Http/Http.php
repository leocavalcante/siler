<?php
/**
 * Helpers for the HTTP abstraction
 */

namespace Siler\Http;

use function Siler\array_get;
use function Siler\require_fn;

/**
 * Get a value from the $_GET global
 *
 * @param string $key The key to be searched
 * @param mixed $default The default value to be returned when the key don't exists
 *
 * @return mixed
 */
function get($key = null, $default = null)
{
    return array_get($_GET, $key, $default);
}

/**
 * Get a value from the $_POST global
 *
 * @param string $key The key to be searched
 * @param mixed $default The default value to be returned when the key don't exists
 *
 * @return mixed
 */
function post($key = null, $default = null)
{
    return array_get($_POST, $key, $default);
}

/**
 * Get a value from the $_REQUEST global
 *
 * @param string $key The key to be searched
 * @param mixed $default The default value to be returned when the key don't exists
 *
 * @return mixed
 */
function input($key = null, $default = null)
{
    return array_get($_REQUEST, $key, $default);
}

/**
 * Redirects using the HTTP Location header
 *
 * @param string $url The url to be redirected to
 */
function redirect($url)
{
    header('Location: '.$url);
}

/**
 * Returns a path based on the projects base url
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
 * Get the current HTTP URI avoding the script name
 *
 * @return string
 */
function path()
{
    $scriptName = array_get($_SERVER, 'SCRIPT_NAME', '');
    $requestUri = array_get($_SERVER, 'REQUEST_URI', '');

    return '/'.ltrim(str_replace(dirname($scriptName), '', $requestUri), '/');
}

/**
 * Get the absolute project's URI
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
    $requestUri = array_get($_SERVER, 'REQUEST_URI', '');

    return $protocol.'://'.$httpHost.$requestUri;
}

/**
 * Check if the request method is POST
 *
 * @return bool
 */
function is_post()
{
    return method_is('post');
}

/**
 * Check if the request method is GET
 *
 * @return bool
 */
function is_get()
{
    return method_is('get');
}

/**
 * Check if the request method is PUT
 *
 * @return bool
 */
function is_put()
{
    return method_is('put');
}

/**
 * Check if the request method is DELETE
 *
 * @return bool
 */
function is_delete()
{
    return method_is('delete');
}

/**
 * Check if the request method is OPTIONS
 *
 * @return bool
 */
function is_options()
{
    return method_is('options');
}

/**
 * Check for a custom HTTP method
 *
 * @param string $method The given method to check on
 *
 * @return bool
 */
function method_is($method)
{
    $requestMethod = array_get($_SERVER, 'REQUEST_METHOD', 'GET');
    return strtolower($method) == strtolower($requestMethod);
}
