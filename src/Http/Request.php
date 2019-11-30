<?php declare(strict_types=1);
/*
 * Helpers functions for HTTP requests.
 */

namespace Siler\Http\Request;

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Siler\Container;
use Siler\Swoole\RequestInterface;
use Swoole\Http\Request;
use function locale_get_default;
use function Siler\array_get;
use function Siler\Encoder\Json\decode;
use const Siler\Swoole\SWOOLE_HTTP_REQUEST;

/**
 * Returns the raw HTTP body request.
 *
 * @param string $input The input file to check on
 *
 * @return string
 */
function raw(string $input = 'php://input'): string
{
    return (string)file_get_contents($input);
}

/**
 * Returns URL decoded raw request body.
 *
 * @param string $input The input file to check on
 *
 * @return array
 */
function params(string $input = 'php://input'): array
{
    $params = [];
    parse_str(raw($input), $params);

    return $params;
}

/**
 * Returns JSON decoded raw request body.
 *
 * @param string $input The input file to check on
 * @return array|string|int|float|object|bool
 */
function json(string $input = 'php://input')
{
    return decode(raw($input));
}

/**
 * Returns all the HTTP headers.
 *
 * @return array<string, string>
 */
function headers(): array
{
    /** @var array<string> $serverKeys */
    $serverKeys = array_keys($_SERVER);
    $httpHeaders = array_reduce(
        $serverKeys,
        function (array $headers, string $key): array {
            if ($key === 'CONTENT_TYPE') {
                $headers[] = $key;
            }

            if ($key === 'CONTENT_LENGTH') {
                $headers[] = $key;
            }

            if (substr($key, 0, 5) === 'HTTP_') {
                $headers[] = $key;
            }

            return $headers;
        },
        []
    );

    $values = array_map(function (string $header): string {
        return strval($_SERVER[$header]);
    }, $httpHeaders);

    $headers = array_map(function (string $header) {
        if (substr($header, 0, 5) == 'HTTP_') {
            $header = substr($header, 5);

            if (false === $header) {
                $header = 'HTTP_';
            }
        }

        return str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $header))));
    }, $httpHeaders);

    return array_combine($headers, $values);
}

/**
 * Returns the request header or the given default.
 *
 * @param string $key The header name
 * @param string|null $default The default value when header isn't present
 *
 * @return string|null
 */
function header(string $key, ?string $default = null)
{
    $val = array_get(headers(), $key, $default, true);

    if (is_array($val)) {
        return null;
    }

    return $val;
}

/**
 * Get a value from the $_GET global.
 *
 * @param string|null $key
 * @param mixed $default The default value to be returned when the key don't exists
 *
 * @return mixed
 */
function get(?string $key = null, $default = null)
{
    /** @var array<string, string> $_GET */
    return array_get($_GET, $key, $default);
}

/**
 * Get a value from the $_POST global.
 *
 * @param string|null $key
 * @param mixed $default The default value to be returned when the key don't exists
 *
 * @return string|array<string, string>|null
 */
function post(?string $key = null, string $default = null)
{
    /** @var array<string, string> $_POST */
    return array_get($_POST, $key, $default);
}

/**
 * Get a value from the $_REQUEST global.
 *
 * @param string|null $key
 * @param mixed $default The default value to be returned when the key don't exists
 *
 * @return string|null|array
 */
function input(?string $key = null, $default = null)
{
    /** @var array<string, string> $_REQUEST */
    return array_get($_REQUEST, $key, $default);
}

/**
 * Get a value from the $_FILES global.
 *
 * @param string|null $key
 * @param mixed $default The default value to be returned when the key don't exists
 * @return array|null|string
 */
function file(?string $key = null, $default = null)
{
    /** @var array<string, array> $_FILES */
    return array_get($_FILES, $key, $default);
}

/**
 * Returns the current HTTP request method.
 * Override with X-Http-Method-Override header or _method on body.
 *
 * @return string
 */
function method(): string
{
    $method = header('X-Http-Method-Override');

    if ($method !== null) {
        return $method;
    }

    /**
     * @var array<string, string> $_POST
     * @var string|null $method
     */
    $method = array_get($_POST, '_method');

    if ($method !== null) {
        return strval($method);
    }

    /**
     * @var array<string, string> $_SERVER
     * @var string|null $method
     */
    $method = array_get($_SERVER, 'REQUEST_METHOD');

    if ($method !== null) {
        return strval($method);
    }

    return 'GET';
}

/**
 * Checks for the current HTTP request method.
 *
 * @param string|array $method The given method to check on
 * @param string|null $requestMethod
 * @return bool
 */
function method_is($method, ?string $requestMethod = null): bool
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
    $languages = [];

    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        // break up string into pieces (languages and q factors)
        preg_match_all(
            '/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i',
            strval($_SERVER['HTTP_ACCEPT_LANGUAGE']),
            $lang_parse
        );

        if (is_countable($lang_parse[1]) && count($lang_parse[1]) > 0) {
            // create a list like "en" => 0.8
            /** @var array<mixed, array-key> $lang_parse_1 */
            $lang_parse_1 = $lang_parse[1];
            /** @var array<mixed, mixed> $lang_parse_4 */
            $lang_parse_4 = $lang_parse[4];
            $languages = array_combine($lang_parse_1, $lang_parse_4);

            /**
             * Set default to 1 for any without q factor
             *
             * @var string $lang
             * @var string $val
             */
            foreach ($languages as $lang => $val) {
                if ($val === '') {
                    $languages[$lang] = 1;
                }
            }

            arsort($languages, SORT_NUMERIC | SORT_DESC);
        }
    } //end if

    return $languages;
}

/**
 * Get locale asked in request, or system default if none found.
 *
 * Priority is as follows:
 *
 * - GET param `lang`: ?lang=en.
 * - Session param `lang`: $_SESSION['lang'].
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
    /** @var array<string, string> $_GET */
    $locale = strval(array_get($_GET, 'lang', ''));

    if (empty($locale)) {
        /** @var array<string, string> $_SESSION */
        $locale = strval(array_get($_SESSION, 'lang', ''));
    }

    if (empty($locale)) {
        $locales = accepted_locales();
        $locale = empty($locales) ? '' : (string)array_keys($locales)[0];
    }

    if (empty($locale)) {
        $locale = $default;
    }

    if (empty($locale) && function_exists('locale_get_default')) {
        $locale = locale_get_default();
    }

    return $locale;
}

/**
 * Look up for Bearer Authorization token.
 *
 * @param null $request
 *
 * @return string|null
 */
function bearer($request = null): ?string
{
    $header = authorization_header($request);

    if (is_null($header)) {
        return null;
    }

    if (!preg_match('/^Bearer/', $header)) {
        return null;
    }

    $bearer = substr($header, 7);

    if (false === $bearer) {
        return null;
    }

    return $bearer;
}

/**
 * Returns the Authorization HTTP header.
 *
 * @param null $request
 *
 * @return string|null
 */
function authorization_header($request = null): ?string
{
    if (Container\has(SWOOLE_HTTP_REQUEST)) {
        /**
         * @psalm-suppress UndefinedDocblockClass
         * @var Request $request
         */
        $request = Container\get(SWOOLE_HTTP_REQUEST);
        /** @var string|null */
        return $request->header['authorization'] ?? null;
    }

    if ($request instanceof ServerRequestInterface) {
        return $request->getHeaderLine('Authorization');
    }

    return header('Authorization');
}
