<?php
/**
 * Helpers functions for HTTP requests
 */

namespace Siler\Http\Request;

use function Siler\array_get;

/**
 * Returns the raw HTTP body request
 *
 * @param string The input file to check on
 *
 * @return string
 */
function raw($input = 'php://input')
{
    return file_get_contents($input);
}

/**
 * Returns URL decoded raw request body
 *
 * @param string The input file to check on
 *
 * @return array
 */
function params($input = 'php://input')
{
    $params = [];
    parse_str(raw($input), $params);
    return $params;
}

/**
 * Returns JSON decoded raw request body
 *
 * @param string The input file to check on
 *
 * @return array
 */
function json($input = 'php://input')
{
    return json_decode(raw($input), true);
}

/**
 * Returns all the HTTP headers
 *
 * @return array
 */
function headers()
{
    $serverKeys = array_keys($_SERVER);
    $httpHeaders = array_filter($serverKeys, function ($key) {
        return substr($key, 0, 5) == 'HTTP_';
    });

    $values = array_map(function ($header) {
        return $_SERVER[$header];
    }, $httpHeaders);

    $headers = array_map(function ($header) {
        return str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($header, 5)))));
    }, $httpHeaders);

    return array_combine($headers, $values);
}

/**
 * Returns the request header or the given default
 *
 * @param  string $key     The header name
 * @param   mixed $default The default value when header isnt present
 *
 * @return string
 */
function header($key, $default = null)
{
    return array_get(headers(), $key, $default);
}

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
