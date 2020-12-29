<?php
/**
 * Helpers functions for HTTP requests.
 * @noinspection PhpComposerExtensionStubsInspection
 */

declare(strict_types=1);

namespace Siler\Http\Request;

use Psr\Http\Message\ServerRequestInterface;
use Siler\Container;
use Swoole\Http\Request;
use function function_exists;
use function in_array;
use function is_array;
use function locale_get_default;
use function Siler\array_get;
use function Siler\array_get_str;
use function Siler\Encoder\Json\decode;
use function Siler\Str\starts_with;
use const Siler\Swoole\SWOOLE_HTTP_REQUEST;

/**
 * Returns the raw HTTP body request.
 *
 * @param string $input The input file to check on
 * @return string
 */
function raw(string $input = 'php://input'): string
{
    if (Container\has(SWOOLE_HTTP_REQUEST)) {
        return (string)\Siler\Swoole\request()->rawContent();
    }

    $contents = file_get_contents($input);
    return $contents === false ? '' : $contents;
}

/**
 * Returns URL decoded raw request body.
 *
 * @param string $input The input file to check on
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
 * Tries to figure out the body type and parse it.
 *
 * @param string $input
 * @return mixed
 */
function body_parse(string $input = 'php://input')
{
    if (is_json()) {
        return json($input);
    }

    return post();
}

/**
 * Returns true if the current HTTP request is JSON (based on Content-type header).
 *
 * @param bool $default
 * @return bool
 */
function is_json(bool $default = false): bool
{
    $content_type = content_type();

    if ($content_type !== null) {
        return starts_with($content_type, 'application/json');
    }

    return $default;
}

/**
 * Returns true if the current request is multipart/form-data, based on Content-type header.
 *
 * @param bool $default
 * @return bool
 */
function is_multipart(bool $default = false): bool
{
    $content_type = content_type();

    if ($content_type !== null) {
        return starts_with($content_type, 'multipart/form-data');
    }

    return $default;
}

/**
 * Returns the Content-type header.
 *
 * @param string|null $default
 * @return string|null
 */
function content_type(?string $default = null): ?string
{
    return header('content-type', $default);
}

/**
 * Returns all the HTTP headers.
 *
 * @return array<string, string>
 */
function headers(): array
{
    if (Container\has(SWOOLE_HTTP_REQUEST)) {
        /** @var array<string, string> $headers */
        $headers = \Siler\Swoole\request()->header;
        return $headers;
    }

    /** @var array<string> $server_keys */
    $server_keys = array_keys($_SERVER);
    $http_headers = array_reduce(
        $server_keys,
        static function (array $headers, string $key): array {
            if ($key === 'CONTENT_TYPE') {
                $headers[] = $key;
            }

            if ($key === 'CONTENT_LENGTH') {
                $headers[] = $key;
            }

            if (strncmp($key, 'HTTP_', 5) === 0) {
                $headers[] = $key;
            }

            return $headers;
        },
        []
    );

    $values = array_map(static function (string $header): string {
        return (string)$_SERVER[$header];
    }, $http_headers);

    $headers = array_map(static function (string $header) {
        if (strncmp($header, 'HTTP_', 5) === 0) {
            $header = substr($header, 5);

            if ($header === false) {
                $header = 'HTTP_';
            }
        }

        return str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $header))));
    }, $http_headers);

    return array_combine($headers, $values);
}

/**
 * Returns the request header or the given default.
 *
 * @param string $key The header name
 * @param string|null $default The default value when header isn't present
 * @return string|null
 */
function header(string $key, ?string $default = null): ?string
{
    if (Container\has(SWOOLE_HTTP_REQUEST)) {
        /** @var array<string, string> $headers */
        $headers = \Siler\Swoole\request()->header;
        return array_get_str($headers, $key, $default);
    }

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
 * @param string|null $default The default value to be returned when the key don't exists
 * @return string|array<string, string|null>|null
 */
function post(?string $key = null, ?string $default = null)
{
    if (Container\has(SWOOLE_HTTP_REQUEST)) {
        /** @var array<string, string> $post */
        $post = \Siler\Swoole\request()->post;
        return array_get($post, $key, $default);
    }

    /** @var array<string, string> $_POST */
    return array_get($_POST, $key, $default);
}

/**
 * Get a value from the $_REQUEST global.
 *
 * @param string|null $key
 * @param string|null $default The default value to be returned when the key don't exists
 *
 * @return string|null|array<string, string|null>
 */
function input(?string $key = null, ?string $default = null)
{
    /** @var array<string, string> $_REQUEST */
    return array_get($_REQUEST, $key, $default);
}

/**
 * Get a value from the $_FILES global.
 *
 * @param array-key|null $key
 * @param array|null $default The default value to be returned when the key don't exists
 * @return array<string, array>|array|null
 */
function file($key = null, ?array $default = null): ?array
{
    if (Container\has(SWOOLE_HTTP_REQUEST)) {
        /** @var array[] $files */
        $files = \Siler\Swoole\request()->files;
        return array_get($files, $key, $default);
    }

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
        return $method;
    }

    /**
     * @var array<string, string> $_SERVER
     * @var string|null $method
     */
    $method = array_get($_SERVER, 'REQUEST_METHOD');

    return $method ?? 'GET';
}

/**
 * Checks for the current HTTP request method.
 *
 * @param string|string[] $method The given method to check on
 * @param string|null $request_method
 * @return bool
 */
function method_is($method, ?string $request_method = null): bool
{
    if ($request_method === null) {
        $request_method = method();
    }

    if (is_array($method)) {
        $method = array_map('strtolower', $method);

        return in_array(strtolower($request_method), $method, true);
    }

    return strtolower($method) === strtolower($request_method);
}

/**
 * Returns the list of accepted languages,
 * sorted by priority, taken from the HTTP_ACCEPT_LANGUAGE super global.
 *
 * @return array Languages by [language => priority], or empty if none could be found.
 */
function accepted_locales(): array
{
    $languages = [];

    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        // break up string into pieces (languages and q factors)
        preg_match_all(
            '/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.\d+))?/i',
            (string) $_SERVER['HTTP_ACCEPT_LANGUAGE'],
            $lang_parse
        );

        if (count($lang_parse) > 1 && count($lang_parse[1]) > 0) {
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
    /** @psalm-var array<string, string> $_GET */
    $locale = array_get_str($_GET, 'lang', '');

    if (empty($locale)) {
        /** @psalm-var array<string, string> $_SESSION */
        $locale = array_get_str($_SESSION, 'lang', '');
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
 * @return string|null
 */
function bearer($request = null): ?string
{
    $header = authorization_header($request);

    if ($header === null) {
        return null;
    }

    if (strncmp($header, 'Bearer', 6) !== 0) {
        return null;
    }

    $bearer = substr($header, 7);

    if ($bearer === false) {
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
        /** @var Request $request */
        $request = Container\get(SWOOLE_HTTP_REQUEST);
        /**
         * @psalm-suppress MissingPropertyType
         * @var array<string, string> $request_header
         */
        $request_header = $request->header;
        return $request_header['authorization'] ?? null;
    }

    if ($request instanceof ServerRequestInterface) {
        return $request->getHeaderLine('Authorization');
    }

    return header('Authorization');
}

/**
 * Returns the HTTP Request User-Agent.
 *
 * @return string|null
 */
function user_agent(): ?string
{
    return header('user-agent');
}
