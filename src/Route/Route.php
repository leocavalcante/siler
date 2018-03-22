<?php declare(strict_types=1);

/**
 * Siler routing facilities.
 */

namespace Siler\Route;

use Psr\Http\Message\ServerRequestInterface;
use Siler\Http;
use Siler\Http\Request;
use function Siler\require_fn;

/**
 * Define a new route using the GET HTTP method.
 *
 * @param string                            $path     The HTTP URI to listen on
 * @param string|callable                   $callback The callable to be executed or a string to be used with Siler\require_fn
 * @param array|ServerRequestInterface|null $request  null, array[method, path] or Psr7 Request Message
 *
 * @return mixed|null
 */
function get(string $path, $callback, $request = null)
{
    return route('get', $path, $callback, $request);
}

/**
 * Define a new route using the POST HTTP method.
 *
 * @param string                            $path     The HTTP URI to listen on
 * @param string|callable                   $callback The callable to be executed or a string to be used with Siler\require_fn
 * @param array|ServerRequestInterface|null $request  null, array[method, path] or Psr7 Request Message
 *
 * @return mixed|null
 */
function post(string $path, $callback, $request = null)
{
    return route('post', $path, $callback, $request);
}

/**
 * Define a new route using the PUT HTTP method.
 *
 * @param string                            $path     The HTTP URI to listen on
 * @param string|callable                   $callback The callable to be executed or a string to be used with Siler\require_fn
 * @param array|ServerRequestInterface|null $request  null, array[method, path] or Psr7 Request Message
 *
 * @return mixed|null
 */
function put(string $path, $callback, $request = null)
{
    return route('put', $path, $callback, $request);
}

/**
 * Define a new route using the DELETE HTTP method.
 *
 * @param string                            $path     The HTTP URI to listen on
 * @param string|callable                   $callback The callable to be executed or a string to be used with Siler\require_fn
 * @param array|ServerRequestInterface|null $request  null, array[method, path] or Psr7 Request Message
 *
 * @return mixed|null
 */
function delete(string $path, $callback, $request = null)
{
    return route('delete', $path, $callback, $request);
}

/**
 * Define a new route using the OPTIONS HTTP method.
 *
 * @param string                            $path     The HTTP URI to listen on
 * @param string|callable                   $callback The callable to be executed or a string to be used with Siler\require_fn
 * @param array|ServerRequestInterface|null $request  null, array[method, path] or Psr7 Request Message
 *
 * @return mixed|null
 */
function options(string $path, $callback, $request = null)
{
    return route('options', $path, $callback, $request);
}

/**
 * Define a new route.
 *
 * @param string|array                      $method   The HTTP request method to listen on
 * @param string                            $path     The HTTP URI to listen on
 * @param string|callable                   $callback The callable to be executed or a string to be used with Siler\require_fn
 * @param array|ServerRequestInterface|null $request  Null, array[method, path] or Psr7 Request Message
 *
 * @return mixed|null
 */
function route($method, string $path, $callback, $request = null)
{
    $path = regexify($path);

    if (is_string($callback)) {
        $callback = require_fn($callback);
    }

    if (is_null($request)) {
        $request = [Request\method(), Http\path()];
    }

    /** @psalm-suppress PossiblyInvalidArgument */
    if (is_a($request, 'Psr\Http\Message\ServerRequestInterface')) {
        $request = [$request->getMethod(), $request->getUri()->getPath()];
    }

    if (count($request) >= 2 &&
        Request\method_is($method, $request[0]) &&
        preg_match($path, $request[1], $params)) {
        return $callback($params);
    }

    return null;
}

/**
 * Turns a URL route path into a Regexp.
 *
 * @param string $path The HTTP path
 *
 * @return string
 */
function regexify(string $path) : string
{
    $path = preg_replace('/\{([A-z-]+)\}/', '(?<$1>[A-z0-9_-]+)', $path);
    $path = "#^{$path}/?$#";

    return $path;
}

/**
 * Creates a resource route path mapping.
 *
 * @param string                            $basePath      The base for the resource
 * @param string                            $resourcesPath The base path name for the corresponding PHP files
 * @param string                            $identityParam The param to be used as identity in the URL
 * @param array|ServerRequestInterface|null $request       null, array[method, path] or Psr7 Request Message
 */
function resource(string $basePath, string $resourcesPath, string $identityParam = null, $request = null)
{
    $basePath = '/' . trim($basePath, '/');
    $resourcesPath = rtrim($resourcesPath, '/');

    if (is_null($identityParam)) {
        $identityParam = 'id';
    }

    get($basePath, $resourcesPath . '/index.php', $request);
    get($basePath . '/create', $resourcesPath . '/create.php', $request);
    get($basePath . '/{' . $identityParam . '}/edit', $resourcesPath . '/edit.php', $request);
    get($basePath . '/{' . $identityParam . '}', $resourcesPath . '/show.php', $request);

    post($basePath, $resourcesPath . '/store.php', $request);
    put($basePath . '/{' . $identityParam . '}', $resourcesPath . '/update.php', $request);
    delete($basePath . '/{' . $identityParam . '}', $resourcesPath . '/destroy.php', $request);
}

/**
 * Maps a filename to a route method-path pair.
 *
 * @param string $filename
 *
 * @return array [HTTP_METHOD, HTTP_PATH]
 */
function routify(string $filename) : array
{
    $filename = str_replace('\\', '/', $filename);
    $filename = trim($filename, '/');
    $filename = str_replace('/', '.', $filename);

    $tokens = array_slice(explode('.', $filename), 0, -1);
    $tokens = array_map(function ($token) {
        if ($token[0] == '$') {
            $token = '{' . substr($token, 1) . '}';
        }

        if ($token[0] == '@') {
            $token = '?{' . substr($token, 1) . '}?';
        }

        return $token;
    }, $tokens);

    $method = array_pop($tokens);
    $path = implode('/', $tokens);
    $path = '/' . trim(str_replace('index', '', $path), '/');

    return [$method, $path];
}

/**
 * Iterates over the given $basePath listening for matching routified files.
 *
 * @param string                            $basePath
 * @param string                            $routePrefix
 * @param array|ServerRequestInterface|null $request     null, array[method, path] or Psr7 Request Message
 *
 * @return void
 */
function files(string $basePath, string $routePrefix = '', $request = null)
{
    $realpath = realpath($basePath);

    if (false === $realpath) {
        throw new \InvalidArgumentException("{$basePath} does not exists");
    }

    $directory = new \RecursiveDirectoryIterator($realpath);
    $iterator = new \RecursiveIteratorIterator($directory);
    $regex = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

    $cut = strlen($realpath);
    $routePrefix = rtrim($routePrefix, '/');

    foreach ($regex as $filename => $file) {
        list($method, $path) = routify(substr($filename, $cut));

        if ('/' === $path) {
            if ($routePrefix) {
                $path = $routePrefix;
            }
        } else {
            $path = $routePrefix . $path;
        }

        route($method, $path, $filename, $request);
    }
}
