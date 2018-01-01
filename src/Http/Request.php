<?php
/**
 * Helpers functions for HTTP requests.
 */

namespace Siler\Http\Request;

use function Siler\array_get;

/**
 * Returns the raw HTTP body request.
 *
 * @param string $input The input file to check on
 *
 * @return string
 */
function raw($input = 'php://input')
{
    return (string) file_get_contents($input);
}

/**
 * Returns URL decoded raw request body.
 *
 * @param string $input The input file to check on
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
 * Returns JSON decoded raw request body.
 *
 * @param string $input The input file to check on
 *
 * @return array
 */
function json($input = 'php://input')
{
    return json_decode(raw($input), true);
}

/**
 * Returns all the HTTP headers.
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
 * Returns the request header or the given default.
 *
 * @param string $key     The header name
 * @param mixed  $default The default value when header isnt present
 *
 * @return mixed
 */
function header($key, $default = null)
{
    return array_get(headers(), $key, $default);
}

/**
 * Get a value from the $_GET global.
 *
 * @param string $key     The key to be searched
 * @param mixed  $default The default value to be returned when the key don't exists
 *
 * @return mixed
 */
function get($key = null, $default = null)
{
    return array_get($_GET, $key, $default);
}

/**
 * Get a value from the $_POST global.
 *
 * @param string $key     The key to be searched
 * @param mixed  $default The default value to be returned when the key don't exists
 *
 * @return mixed
 */
function post($key = null, $default = null)
{
    return array_get($_POST, $key, $default);
}

/**
 * Get a value from the $_REQUEST global.
 *
 * @param string $key     The key to be searched
 * @param mixed  $default The default value to be returned when the key don't exists
 *
 * @return mixed
 */
function input($key = null, $default = null)
{
    return array_get($_REQUEST, $key, $default);
}

/**
 * Get a value from the $_FILES global.
 *
 * @param string $key     The key to be searched
 * @param mixed  $default The default value to be returned when the key don't exists
 *
 * @return mixed
 */
function file($key = null, $default = null)
{
    return array_get($_FILES, $key, $default);
}

/**
 * Returns the current HTTP request method.
 * Override with X-Http-Method-Override header or _method on body.
 *
 * @return string
 */
function method()
{
    if ($method = header('X-Http-Method-Override')) {
        return $method;
    }

    if ($method = array_get($_POST, '_method')) {
        return $method;
    }

    if ($method = array_get($_SERVER, 'REQUEST_METHOD')) {
        return $method;
    }

    return 'GET';
}

/**
 * Checks for the current HTTP request method.
 *
 * @param string|array $method The given method to check on
 *
 * @return bool
 */
function method_is($method, $requestMethod = null)
{
    if (is_null($requestMethod)) {
        $requestMethod = method();
    }

    if (is_array($method)) {
        $method = array_map('strtolower', $method);

        return in_array(strtolower($requestMethod), $method);
    }

    return strtolower($method) == strtolower($requestMethod);
}

/**
 * Returns the list of accepted languages,
 * sorted by priority, taken from the HTTP_ACCEPT_LANGUAGE superglobal.
 *
 * @return array Languages by [language => priority], or empty if none could be found.
 */
function accepted_locales(): array
{
    $langs = [];

    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        // break up string into pieces (languages and q factors)
        preg_match_all(
            '/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i',
            $_SERVER['HTTP_ACCEPT_LANGUAGE'],
            $lang_parse
        );

        if (count($lang_parse[1])) {
            // create a list like "en" => 0.8
            $langs = array_combine($lang_parse[1], $lang_parse[4]);

            // set default to 1 for any without q factor
            foreach ($langs as $lang => $val) {
                if ($val === '') {
                    $langs[$lang] = 1;
                }
            }

            arsort($langs, SORT_NUMERIC | SORT_DESC);
        }
    }

    return $langs;
}
/**
 * Get locale asked in request, or system default if none found.
 *
 * Priority is as follows:
 *
 * - GET param `lang`: ?lang=en.
 * - Session param `lang`: $_SESSION['lang].
 * - Most requested locale as given by accepted_locales().
 * - Fallback locale, passed in parameter (optional).
 * - Default system locale.
 *
 * @param string $default Fallback locale to use if nothing could be selected, just before default system locale.
 *
 * @return string selected locale.
 */
function recommended_locale(string $default = ''): string
{
    $locale = $_GET['lang'] ?? '';

    if (empty($locale)) {
        $locale = $_SESSION['lang'] ?? '';
    }
    if (empty($locale)) {
        $locales = accepted_locales();
        $locale = empty($locales) ? '' : array_keys($locales)[0];
    }
    if (empty($locale)) {
        $locale = $default;
    }
    if (empty($locale)) {
        $locale = \locale_get_default();
    }

    return $locale;
}
