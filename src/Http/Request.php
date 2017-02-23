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
