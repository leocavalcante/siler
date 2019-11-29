<?php

declare(strict_types=1);

/*
 * Siler routing facilities.
 */

namespace Siler\Route;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use RegexIterator;
use Siler\Container;
use Siler\Http;
use Siler\Http\Request;
use function Siler\require_fn;
use const Siler\Swoole\SWOOLE_HTTP_REQUEST;

const DID_MATCH = 'route_did_match';
const STOP_PROPAGATION = 'route_stop_propagation';
const CANCEL = 'route_cancel';

/**
 * Define a new route using the GET HTTP method.
 *
 * @param string $path The HTTP URI to listen on
 * @param string|callable $callback The callable to be executed or a string to be used with Siler\require_fn
 * @param array|ServerRequestInterface|null $request null, array[method, path] or Psr7 Request Message
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
 * @param string $path The HTTP URI to listen on
 * @param string|callable $callback The callable to be executed or a string to be used with Siler\require_fn
 * @param array|ServerRequestInterface|null $request null, array[method, path] or Psr7 Request Message
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
 * @param string $path The HTTP URI to listen on
 * @param string|callable $callback The callable to be executed or a string to be used with Siler\require_fn
 * @param array|ServerRequestInterface|null $request null, array[method, path] or Psr7 Request Message
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
 * @param string $path The HTTP URI to listen on
 * @param string|callable $callback The callable to be executed or a string to be used with Siler\require_fn
 * @param array|ServerRequestInterface|null $request null, array[method, path] or Psr7 Request Message
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
 * @param string $path The HTTP URI to listen on
 * @param string|callable $callback The callable to be executed or a string to be used with Siler\require_fn
 * @param array|ServerRequestInterface|null $request null, array[method, path] or Psr7 Request Message
 *
 * @return mixed|null
 */
function options(string $path, $callback, $request = null)
{
    return route('options', $path, $callback, $request);
}

/**
 * Define a new route using the any HTTP method.
 *
 * @param string $path The HTTP URI to listen on
 * @param string|callable $callback The callable to be executed or a string to be used with Siler\require_fn
 * @param array|ServerRequestInterface|null $request null, array[method, path] or Psr7 Request Message
 *
 * @return mixed|null
 */
function any(string $path, $callback, $request = null)
{
    return route('any', $path, $callback, $request);
}

/**
 * Define a new route.
 *
 * @param string|array $method The HTTP request method to listen on
 * @param string $path The HTTP URI to listen on
 * @param string|callable $callback The callable to be executed or a string to be used with Siler\require_fn
 * @param array|ServerRequestInterface|null $request Null, array[method, path] or Psr7 Request Message
 *
 * @return mixed|null
 */
function route($method, string $path, $callback, $request = null)
{
    if (canceled()) {
        return null;
    }

    if (did_match() && Container\get(STOP_PROPAGATION, true)) {
        return null;
    }

    $path = regexify($path);

    if (is_string($callback)) {
        $callback = require_fn($callback);
    }

    $methodPath = method_path($request);

    if (
        count($methodPath) >= 2 &&
        (Request\method_is($method, $methodPath[0]) ||
        $method == 'any') &&
        preg_match($path, $methodPath[1], $params)
    ) {
        Container\set(DID_MATCH, true);
        return $callback($params);
    }

    return null;
}

/**
 * @param mixed $request null, array[method, path], PSR-7 Request Message or Swoole HTTP request.
 *
 * @return array
 * @internal Used to guess the given request method and path.
 *
 */
function method_path($request): array
{
    if (is_array($request)) {
        return $request;
    }

    if ($request instanceof ServerRequestInterface) {
        return [$request->getMethod(), $request->getUri()->getPath()];
    }

    if (Container\has(SWOOLE_HTTP_REQUEST)) {
        $request = Container\get(SWOOLE_HTTP_REQUEST);

        return [$request->server['request_method'], $request->server['request_uri']];
    }

    return [Request\method(), Http\path()];
}

/**
 * Turns a URL route path into a Regexp.
 *
 * @param string $path The HTTP path
 *
 * @return string
 */
function regexify(string $path): string
{
    $patterns = [
        '/{([A-z-]+)}/' => '(?<$1>[A-z0-9_-]+)',
        '/{([A-z-]+):(.*)}/' => '(?<$1>$2)',
    ];

    $path = preg_replace(array_keys($patterns), array_values($patterns), $path);
    return "#^{$path}/?$#";
}

/**
 * Creates a resource route path mapping.
 *
 * @param string $basePath The base for the resource
 * @param string $resourcesPath The base path name for the corresponding PHP files
 * @param string|null $identityParam
 * @param array|ServerRequestInterface|null $request null, array[method, path] or Psr7 Request Message
 *
 * @return mixed|null
 */
function resource(string $basePath, string $resourcesPath, ?string $identityParam = null, $request = null)
{
    $basePath = '/' . trim($basePath, '/');
    $resourcesPath = rtrim($resourcesPath, '/');

    if (is_null($identityParam)) {
        $identityParam = 'id';
    }

    $routes = [
        function () use ($basePath, $resourcesPath, $request) {
            return get($basePath, $resourcesPath . '/index.php', $request);
        },
        function () use ($basePath, $resourcesPath, $request) {
            return get($basePath . '/create', $resourcesPath . '/create.php', $request);
        },
        function () use ($basePath, $resourcesPath, $request, $identityParam) {
            return get($basePath . '/{' . $identityParam . '}/edit', $resourcesPath . '/edit.php', $request);
        },
        function () use ($basePath, $resourcesPath, $request, $identityParam) {
            return get($basePath . '/{' . $identityParam . '}', $resourcesPath . '/show.php', $request);
        },
        function () use ($basePath, $resourcesPath, $request) {
            return post($basePath, $resourcesPath . '/store.php', $request);
        },
        function () use ($basePath, $resourcesPath, $request, $identityParam) {
            return put($basePath . '/{' . $identityParam . '}', $resourcesPath . '/update.php', $request);
        },
        function () use ($basePath, $resourcesPath, $request, $identityParam) {
            return delete($basePath . '/{' . $identityParam . '}', $resourcesPath . '/destroy.php', $request);
        },
    ];

    foreach ($routes as $route) {
        $result = $route();

        if (!is_null($result)) {
            return $result;
        }
    }

    return null;
}

/**
 * Maps a filename to a route method-path pair.
 *
 * @param string $filename
 *
 * @return (null|string)[] [HTTP_METHOD, HTTP_PATH]
 *
 * @psalm-return array{0: null|string, 1: string}
 */
function routify(string $filename): array
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
 * @param string $basePath
 * @param string $prefix
 * @param array|ServerRequestInterface|null $request null, array[method, path] or Psr7 Request Message
 *
 * @return mixed|null
 */
function files(string $basePath, string $prefix = '', $request = null)
{
    $realpath = realpath($basePath);

    if (false === $realpath) {
        throw new InvalidArgumentException("{$basePath} does not exists");
    }

    $directory = new RecursiveDirectoryIterator($realpath);
    $iterator = new RecursiveIteratorIterator($directory);
    $regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

    $files = array_keys(iterator_to_array($regex));

    sort($files);

    $cut = strlen($realpath);
    $prefix = rtrim($prefix, '/');

    foreach ($files as $filename) {
        $cutFilename = substr((string)$filename, $cut);

        if (false === $cutFilename) {
            continue;
        }

        list($method, $path) = routify($cutFilename);

        if ('/' === $path) {
            if ($prefix) {
                $path = $prefix;
            }
        } else {
            $path = $prefix . $path;
        }

        $result = route($method, $path, (string)$filename, $request);

        if (!is_null($result)) {
            return $result;
        }
    }

    return null;
}

/**
 * Uses a class name to create routes based on its public methods.
 *
 * @param string $basePath The prefix for all routes
 * @param string $className The qualified class name
 * @param array|ServerRequestInterface|null $request null, array[method, path] or Psr7 Request Message
 *
 * @throws ReflectionException
 *
 * @return void
 */
function class_name(string $basePath, string $className, $request = null): void
{
    $reflection = new ReflectionClass($className);
    $object = $reflection->newInstance();

    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

    foreach ($methods as $method) {
        $specs = preg_split('/(?=[A-Z])/', $method->name);

        $pathSegments = array_map('strtolower', array_slice($specs, 1));

        $pathSegments = array_filter($pathSegments, function (string $segment): bool {
            return $segment != 'index';
        });

        $pathParams = array_map(function (ReflectionParameter $param) {
            return "{{$param->name}}";
        }, $method->getParameters());

        $pathSegments = array_merge($pathSegments, $pathParams);

        array_unshift($pathSegments, $basePath);

        route(
            $specs[0],
            join('/', $pathSegments),
            function (array $params) use ($method, $object) {
                foreach (array_keys($params) as $key) {
                    if (!is_int($key)) {
                        unset($params[$key]);
                    }
                }

                $args = array_slice($params, 1);
                $method->invokeArgs($object, $args);
            },
            $request
        );
    } //end foreach
}

/**
 * Avoids routes to be called after the first match.
 *
 * @return void
 */
function stop_propagation(): void
{
    Container\set(STOP_PROPAGATION, true);
}

/**
 * Avoids routes to be called even on a match.
 *
 * @return void
 */
function cancel(): void
{
    Container\set(CANCEL, true);
}

/**
 * Returns true if routing is canceled.
 *
 * @return bool
 */
function canceled(): bool
{
    return Container\get(CANCEL, false);
}

/**
 * Resets default routing behaviour.
 *
 * @return void
 */
function resume(): void
{
    Container\set(STOP_PROPAGATION, false);
    Container\set(CANCEL, false);
}

/**
 * Returns the first non-null route result.
 *
 * @param array $routes The route results to br tested
 *
 * @return mixed|null
 */
function match(array $routes)
{
    foreach ($routes as $route) {
        if (!is_null($route)) {
            return $route;
        }
    }

    return null;
}

/**
 * Returns true if a Route has a match.
 *
 * @return bool
 */
function did_match(): bool
{
    return Container\get(DID_MATCH, false);
}

/**
 * Invalidate a route match.
 */
function purge_match(): void
{
    Container\set(DID_MATCH, false);
}
