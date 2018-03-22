<?php

declare(strict_types=1);

/**
 * Helpers for the HTTP abstraction.
 */

namespace Siler\Http;

use Psr\Http\Message\ServerRequestInterface;
use function Siler\array_get;

/**
 * Get a value from the $_COOKIE global.
 *
 * @param string $key     The key to be searched
 * @param mixed  $default The default value to be returned when the key don't exists
 *
 * @return mixed
 */
function cookie(string $key = null, $default = null)
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
function session(string $key = null, $default = null)
{
    return array_get($_SESSION, $key, $default);
}

/**
 * Set a value in the $_SESSION global.
 *
 * @param string $key   The key to be used
 * @param mixed  $value The value to be stored
 */
function setsession(string $key, $value)
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
 */
function flash(string $key = null, $default = null)
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
 */
function redirect(string $url)
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
function url(string $path = null) : string
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
function path() : string
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
function uri(string $protocol = null) : string
{
    $https = array_get($_SERVER, 'HTTPS', '');

    if (is_null($protocol)) {
        $protocol = empty($https) ? 'http' : 'https';
    }

    $httpHost = array_get($_SERVER, 'HTTP_HOST', '');

    return $protocol.'://'.$httpHost.path();
}

/**
 * Event-driven, non-blocking I/O with PHP.
 *
 * @param callable $handler Callable to execute for requests
 * @param callable $err     Callable to execute for exceptions
 *
 * @return \Closure Address to listen, default 0.0.0.0:8080 -> \React\EventLoop\LoopInterface
 */
function server(callable $handler, callable $err = null) : \Closure
{
    return function (string $addr = '0.0.0.0:8080') use ($handler, $err) : \React\EventLoop\LoopInterface {
        $loop = \React\EventLoop\Factory::create();

        $server = new \React\Http\Server(function (ServerRequestInterface $request) use ($handler, $err) {
            return new \React\Promise\Promise(function (callable $resolve, callable $reject) use ($handler, $err, $request) {
                try {
                    $resolve($handler($request));
                } catch (\Throwable $e) {
                    if (is_null($err)) {
                        return $reject($e);
                    }

                    $resolve($err($e));
                }
            });
        });

        $socket = new \React\Socket\Server($addr, $loop);
        $server->listen($socket);

        return $loop;
    };
}
