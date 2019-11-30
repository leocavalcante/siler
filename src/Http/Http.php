<?php declare(strict_types=1);
/*
 * Helpers for the HTTP abstraction.
 */

namespace Siler\Http;

use function Siler\array_get;

/**
 * Get a value from the $_COOKIE global.
 *
 * @param string|null $key
 * @param mixed $default The default value to be returned when the key don't exists
 *
 * @return mixed
 */
function cookie(?string $key = null, $default = null)
{
    /** @var array<string, string> $_COOKIE */
    return array_get($_COOKIE, $key, $default);
}

/**
 * Get a value from the $_SESSION global.
 *
 * @param string|null $key
 * @param mixed $default The default value to be returned when the key don't exists
 * @return string|null|array
 */
function session(?string $key = null, string $default = null)
{
    /** @var array<string, string> $_SESSION */
    return array_get($_SESSION, $key, $default);
}

/**
 * Set a value in the $_SESSION global.
 *
 * @param string $key The key to be used
 * @param mixed $value The value to be stored
 *
 * @return void
 */
function setsession(string $key, $value): void
{
    $_SESSION[$key] = strval($value);
}

/**
 * Get a value from the $_SESSION global and remove it.
 *
 * @param string|null $key
 * @param mixed $default The default value to be returned when the key don't exists
 *
 * @return mixed
 */
function flash(?string $key = null, $default = null)
{
    $value = session($key, $default);

    if (!is_null($key)) {
        unset($_SESSION[$key]);
    }

    return $value;
}

/**
 * Redirects using the HTTP Location header.
 *
 * @param string $url The url to be redirected to
 *
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
    if (is_null($path)) {
        $path = '/';
    }

    /**
     * @var array<string, string> $_SERVER
     * @var string $scriptName
     */
    $scriptName = array_get($_SERVER, 'SCRIPT_NAME', '');

    return rtrim(str_replace('\\', '/', dirname($scriptName)), '/') . '/' . ltrim($path, '/');
}

/**
 * Get the current HTTP path info.
 *
 * @return string
 */
function path(): string
{
    /**
     * @var array<string, string> $_SERVER
     * @var string $scriptName
     */
    $scriptName = array_get($_SERVER, 'SCRIPT_NAME', '');
    /** @var string $queryString */
    $queryString = array_get($_SERVER, 'QUERY_STRING', '');
    /** @var string $requestUri */
    $requestUri = array_get($_SERVER, 'REQUEST_URI', '');

    $requestUri = str_replace('?' . $queryString, '', $requestUri);
    $scriptPath = str_replace('\\', '/', dirname($scriptName));

    if (!strlen(str_replace('/', '', $scriptPath))) {
        return '/' . ltrim($requestUri, '/');
    } else {
        return '/' . ltrim(preg_replace("#^$scriptPath#", '', $requestUri, 1), '/');
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

    /** @var string $httpHost */
    $httpHost = array_get($_SERVER, 'HTTP_HOST', '');

    return $protocol . '://' . $httpHost . path();
}
