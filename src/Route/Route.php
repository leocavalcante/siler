<?php
/**
 * Siler routing facilities.
 */

namespace Siler\Route;

use Psr\Http\Message\ServerRequestInterface;
use Siler\Container;
use Siler\Http;
use Siler\Http\Request;
use function Siler\require_fn;

/**
 * Define a new route using the GET HTTP method.
 *
 * @param string          $path     The HTTP URI to listen on
 * @param string|callable $callback The callable to be executed or a string to be used with Siler\require_fn
 */
function get($path, $callback)
{
    return route('get', $path, $callback);
}

/**
 * Define a new route using the POST HTTP method.
 *
 * @param string          $path     The HTTP URI to listen on
 * @param string|callable $callback The callable to be executed or a string to be used with Siler\require_fn
 */
function post($path, $callback)
{
    return route('post', $path, $callback);
}

/**
 * Define a new route using the PUT HTTP method.
 *
 * @param string          $path     The HTTP URI to listen on
 * @param string|callable $callback The callable to be executed or a string to be used with Siler\require_fn
 */
function put($path, $callback)
{
    return route('put', $path, $callback);
}

/**
 * Define a new route using the DELETE HTTP method.
 *
 * @param string          $path     The HTTP URI to listen on
 * @param string|callable $callback The callable to be executed or a string to be used with Siler\require_fn
 */
function delete($path, $callback)
{
    return route('delete', $path, $callback);
}

/**
 * Define a new route using the OPTIONS HTTP method.
 *
 * @param string          $path     The HTTP URI to listen on
 * @param string|callable $callback The callable to be executed or a string to be used with Siler\require_fn
 */
function options($path, $callback)
{
    return route('options', $path, $callback);
}

/**
 * Define a new route.
 *
 * @param string|array    $method   The HTTP request method to listen on
 * @param string          $path     The HTTP URI to listen on
 * @param string|callable $callback The callable to be executed or a string to be used with Siler\require_fn
 *
 * @return mixed|null
 */
function route($method, $path, $callback)
{
    $path = regexify($path);

    if (is_string($callback)) {
        $callback = require_fn($callback);
    }

    if ($request = Container\get('psr7_request')) {
        if (Request\method_is($method, $request->getMethod()) &&
            preg_match($path, $request->getUri()->getPath(), $params)
        ) {
            return $callback($params);
        } else {
            return null;
        }
    }

    if (Request\method_is($method) && preg_match($path, Http\path(), $params)) {
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
 * @param string $basePath      The base for the resource
 * @param string $resourcesPath The base path name for the corresponding PHP files
 * @param string $identityParam The param to be used as identity in the URL
 */
function resource($basePath, $resourcesPath, $identityParam = null)
{
    $basePath = '/'.trim($basePath, '/');
    $resourcesPath = rtrim($resourcesPath, '/');

    if (is_null($identityParam)) {
        $identityParam = 'id';
    }

    get($basePath, $resourcesPath.'/index.php');
    get($basePath.'/create', $resourcesPath.'/create.php');
    get($basePath.'/{'.$identityParam.'}/edit', $resourcesPath.'/edit.php');
    get($basePath.'/{'.$identityParam.'}', $resourcesPath.'/show.php');

    post($basePath, $resourcesPath.'/store.php');
    put($basePath.'/{'.$identityParam.'}', $resourcesPath.'/update.php');
    delete($basePath.'/{'.$identityParam.'}', $resourcesPath.'/destroy.php');
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
 */
function files($basePath)
{
    $directory = new \RecursiveDirectoryIterator($basePath);
    $iterator = new \RecursiveIteratorIterator($directory);
    $regex = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

    foreach ($regex as $filename => $file) {
        list($method, $path) = routify(substr($filename, strlen(rtrim($basePath, '/'))));
        route($method, $path, $filename);
    }
}

/**
 * Define the current HTTP PSR-7 compilant request message.
 *
 * @param ServerRequestInterface $request
 */
function psr7(ServerRequestInterface $request)
{
    Container\set('psr7_request', $request);
}
