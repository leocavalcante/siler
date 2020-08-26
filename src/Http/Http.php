<?php declare(strict_types=1);
/*
 * Helpers for the HTTP abstraction.
 */

namespace Siler\Http;

use function Siler\array_get;
use function Siler\array_get_str;

/**
 * Get a value from the $_COOKIE global.
 *
 * @param string|null $key
 * @param string|null $default The default value to be returned when the key don't exists
 * @return string|null|array<string, string>
 */
function cookie(?string $key = null, ?string $default = null)
{
    /** @var array<string, string> $_COOKIE */
    return array_get($_COOKIE, $key, $default);
}

/**
 * Get a value from the $_SESSION global.
 *
 * @param string|null $key
 * @param string|null $default The default value to be returned when the key don't exists
 * @return string|null|array<string, string>
 */
function session(?string $key = null, ?string $default = null)
{
    /** @var array<string, string> $_SESSION */
    return array_get($_SESSION, $key, $default);
}

/**
 * Set a value in the $_SESSION global.
 *
 * @param string $key The key to be used
 * @param mixed $value The value to be stored
 * @return void
 */
function setsession(string $key, $value): void
{
    $_SESSION[$key] = (string)$value;
}

/**
 * Get a value from the $_SESSION global and remove it.
 *
 * @param string|null $key
 * @param string|null $default The default value to be returned when the key don't exists
 *
 * @return mixed
 */
function flash(?string $key = null, ?string $default = null)
{
    $value = session($key, $default);

    if ($key !== null) {
        unset($_SESSION[$key]);
    }

    return $value;
}

/**
 * Redirects using the HTTP Location header.
 *
 * @param string $url The url to be redirected to
 * @return void
 */
function redirect(string $url): void
{
    Response\header('Location', $url);
}

/**
 * Returns a path based on the projects base url.
 *
 * @param string|null $path
 * @return string
 */
function url(?string $path = null): string
{
    if ($path === null) {
        $path = '/';
    }

    /**
     * @var array<string, string> $_SERVER
     * @var string $script_name
     */
    $script_name = array_get($_SERVER, 'SCRIPT_NAME', '');

    return rtrim(str_replace('\\', '/', dirname($script_name)), '/') . '/' . ltrim($path, '/');
}

/**
 * Get the current HTTP path info.
 *
 * @return string
 */
function path(): string
{
    /** @psalm-var array<string, string> $_SERVER */
    $script_name = array_get_str($_SERVER, 'SCRIPT_NAME', '');
    $query_string = array_get_str($_SERVER, 'QUERY_STRING', '');
    $request_uri = array_get_str($_SERVER, 'REQUEST_URI', '');

    // NOTE: When using built-in server with a router script, SCRIPT_NAME will be same as the REQUEST_URI
    if (php_sapi_name() === 'cli-server') {
        $script_name = '';
    }

    $request_uri = str_replace('?' . $query_string, '', $request_uri);
    $request_uri = rawurldecode($request_uri);
    $script_path = str_replace('\\', '/', dirname($script_name));

    if (!strlen(str_replace('/', '', $script_path))) {
        return '/' . ltrim($request_uri, '/');
    } else {
        return '/' . ltrim(preg_replace("#^$script_path#", '', $request_uri, 1), '/');
    }
}

/**
 * Get the absolute project's URI.
 *
 * @param string|null $protocol
 * @return string
 */
function uri(?string $protocol = null): string
{
    /**
     * @var array<string, string> $_SERVER
     * @var string $https
     */
    $https = array_get($_SERVER, 'HTTPS', '');

    if ($protocol === null) {
        $protocol = empty($https) ? 'http' : 'https';
    }

    /** @var string $http_host */
    $http_host = array_get($_SERVER, 'HTTP_HOST', '');

    return $protocol . '://' . $http_host . path();
}
