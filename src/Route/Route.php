<?php
/**
 * Siler routing facilities.
 */

namespace Siler\Route;

use Siler\Http;
use Siler\Http\Request;
use function Siler\require_fn;

/**
 * Define a new route using the GET HTTP method.
 *
 * @param string          $path                      The HTTP URI to listen on
 * @param string|callable $callback                  The callable to be executed or a string to be used with Siler\require_fn
 * @param array|ServerRequestInterface|null $request null, array[method, path] or Psr7 Request Message
 */
function get($path, $callback, $request = null)
{
    return route('get', $path, $callback, $request);
}

/**
 * Define a new route using the POST HTTP method.
 *
 * @param string          $path                      The HTTP URI to listen on
 * @param string|callable $callback                  The callable to be executed or a string to be used with Siler\require_fn
 * @param array|ServerRequestInterface|null $request null, array[method, path] or Psr7 Request Message
 */
function post($path, $callback, $request = null)
{
    return route('post', $path, $callback, $request);
}

/**
 * Define a new route using the PUT HTTP method.
 *
 * @param string          $path                      The HTTP URI to listen on
 * @param string|callable $callback                  The callable to be executed or a string to be used with Siler\require_fn
 * @param array|ServerRequestInterface|null $request null, array[method, path] or Psr7 Request Message
 */
function put($path, $callback, $request = null)
{
    return route('put', $path, $callback, $request);
}

/**
 * Define a new route using the DELETE HTTP method.
 *
 * @param string          $path                      The HTTP URI to listen on
 * @param string|callable $callback                  The callable to be executed or a string to be used with Siler\require_fn
 * @param array|ServerRequestInterface|null $request null, array[method, path] or Psr7 Request Message
 */
function delete($path, $callback, $request = null)
{
    return route('delete', $path, $callback, $request);
}

/**
 * Define a new route using the OPTIONS HTTP method.
 *
 * @param string          $path                      The HTTP URI to listen on
 * @param string|callable $callback                  The callable to be executed or a string to be used with Siler\require_fn
 * @param array|ServerRequestInterface|null $request null, array[method, path] or Psr7 Request Message
 */
function options($path, $callback, $request = null)
{
    return route('options', $path, $callback, $request);
}

/**
 * Define a new route.
 *
 * @param string|array    $method                    The HTTP request method to listen on
 * @param string          $path                      The HTTP URI to listen on
 * @param string|callable $callback                  The callable to be executed or a string to be used with Siler\require_fn
 * @param array|ServerRequestInterface|null $request Null, array[method, path] or Psr7 Request Message
 *
 * @return mixed|null
 */
function route($method, $path, $callback, $request = null)
{
    $path = regexify($path);
    $isO  = is_a($request, 'Psr\Http\Message\ServerRequestInterface');

    if (is_string($callback)) {
        $callback = require_fn($callback);
    }
    if (!$isO) {
        $request = array_values((array) $request);
    }

    if (
        Request\method_is(
            $method,
            $isO ? $request->getMethod() : ($request[0] ?? Request\method())
        )
        && preg_match(
            $path,
            $isO ? $request->getUri()->getPath() : ($request[1] ?? Http\path()),
            $params
        )
    ) {
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
function regexify($path)
{
    $path = preg_replace('/\{([A-z-]+)\}/', '(?<$1>[A-z0-9_-]+)', $path);
    $path = "#^{$path}/?$#";

    return $path;
}

/**
 * Creates a resource route path mapping.
 *
 * @param string $basePath                           The base for the resource
 * @param string $resourcesPath                      The base path name for the corresponding PHP files
 * @param string $identityParam                      The param to be used as identity in the URL
 * @param array|ServerRequestInterface|null $request null, array[method, path] or Psr7 Request Message
 */
function resource($basePath, $resourcesPath, $identityParam = null, $request = null)
{
    $basePath = '/'.trim($basePath, '/');
    $resourcesPath = rtrim($resourcesPath, '/');

    if (is_null($identityParam)) {
        $identityParam = 'id';
    }

    get($basePath, $resourcesPath.'/index.php', $request);
    get($basePath.'/create', $resourcesPath.'/create.php', $request);
    get($basePath.'/{'.$identityParam.'}/edit', $resourcesPath.'/edit.php', $request);
    get($basePath.'/{'.$identityParam.'}', $resourcesPath.'/show.php', $request);

    post($basePath, $resourcesPath.'/store.php', $request);
    put($basePath.'/{'.$identityParam.'}', $resourcesPath.'/update.php', $request);
    delete($basePath.'/{'.$identityParam.'}', $resourcesPath.'/destroy.php', $request);
}

/**
 * Maps a filename to a route method-path pair.
 *
 * @param string $filename
 *
 * @return array [HTTP_METHOD, HTTP_PATH]
 */
function routify($filename)
{
    $filename = str_replace('\\', '/', $filename);
    $filename = trim($filename, '/');
    $filename = str_replace('/', '.', $filename);

    $tokens = array_slice(explode('.', $filename), 0, -1);
    $tokens = array_map(function ($token) {
        if ($token[0] == '$') {
            $token = '{'.substr($token, 1).'}';
        }

        if ($token[0] == '@') {
            $token = '?{'.substr($token, 1).'}?';
        }

        return $token;
    }, $tokens);

    $method = array_pop($tokens);
    $path = implode('/', $tokens);
    $path = '/'.trim(str_replace('index', '', $path), '/');

    return [$method, $path];
}

/**
 * Iterates over the given $basePath listening for matching routified files.
 *
 * @param string $basePath
 * @param array|ServerRequestInterface|null $request null, array[method, path] or Psr7 Request Message
 */
function files($basePath, $routePrefix = '', $request = null)
{
    $realpath = realpath($basePath);

    if (false === $realpath) {
        user_error(sprintf('Path does not exists: %s', $basePath), E_USER_ERROR);
    }

    $directory = new \RecursiveDirectoryIterator($realpath);
    $iterator = new \RecursiveIteratorIterator($directory);
    $regex = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

    $cut = strlen($realpath);
    $routePrefix = rtrim($routePrefix, '/');
    foreach ($regex as $filename => $file) {
        list($method, $path) = routify(substr($filename, $cut));
        route($method, $routePrefix . $path, $filename, $request);
    }
}
