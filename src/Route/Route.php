<?php declare(strict_types=1);
/*
 * Siler routing facilities.
 */

namespace Siler\Route;

use Psr\Http\Message\ServerRequestInterface;
use Siler\Container;
use Siler\Http;
use Siler\Http\Request;
use Swoole\Http\Request as SwooleRequest;
use function Siler\require_fn;
use const Siler\Swoole\SWOOLE_HTTP_REQUEST;

const DID_MATCH = 'route_did_match';
const STOP_PROPAGATION = 'route_stop_propagation';
const CANCEL = 'route_cancel';
const BASE_PATH = 'route_base_path';

/**
 * Define a new route using the GET HTTP method.
 *
 * @param string $path The HTTP URI to listen on
 * @param string|callable $callback The callable to be executed or a string to be used with Siler\require_fn
 * @param array{0: string, 1: string}|ServerRequestInterface|null $request
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
 * @param array{0: string, 1: string}|ServerRequestInterface|null $request
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
 * @param array{0: string, 1: string}|ServerRequestInterface|null $request
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
 * @param array{0: string, 1: string}|ServerRequestInterface|null $request
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
 * @param array{0: string, 1: string}|ServerRequestInterface|null $request
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
 * @param array{0: string, 1: string}|ServerRequestInterface|null $request
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
 * @param array{0: string, 1: string}|ServerRequestInterface|null $request
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

    if (is_string($callback) && !is_callable($callback)) {
        $callback = require_fn($callback);
    }

    $method_path = method_path($request);

    if (
        count($method_path) >= 2 &&
        (Request\method_is($method, strval($method_path[0])) ||
            $method == 'any') &&
        preg_match($path, strval($method_path[1]), $params)
    ) {
        Container\set(DID_MATCH, true);
        return $callback($params);
    }

    return null;
}

/**
 * @param array{0: string, 1: string}|ServerRequestInterface|null $request
 *
 * @return array{0: string, 1: string}
 * @internal Used to guess the given request method and path.
 *
 */
function method_path($request = null): array
{
    if (is_array($request)) {
        return $request;
    }

    if ($request instanceof ServerRequestInterface) {
        return [$request->getMethod(), $request->getUri()->getPath()];
    }

    if (Container\has(SWOOLE_HTTP_REQUEST)) {
        /** @var SwooleRequest $request */
        $request = Container\get(SWOOLE_HTTP_REQUEST);
        /**
         * @psalm-suppress MissingPropertyType
         * @var array<string, string> $request_server
         */
        $request_server = $request->server;
        return [$request_server['request_method'], $request_server['request_uri']];
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
    /** @var string $base */
    $base = Container\get(BASE_PATH, '');

    return "#^{$base}{$path}/?$#";
}

/**
 * Creates a resource route path mapping.
 *
 * @param string $base_path The base for the resource
 * @param string $resources_path The base path name for the corresponding PHP files
 * @param string|null $identity_param
 * @param array{0: string, 1: string}|ServerRequestInterface|null $request
 *
 * @return mixed|null
 */
function resource(string $base_path, string $resources_path, ?string $identity_param = null, $request = null)
{
    $base_path = '/' . trim($base_path, '/');
    $resources_path = rtrim($resources_path, '/');

    if (is_null($identity_param)) {
        $identity_param = 'id';
    }

    /** @var array<\Closure(): mixed> $routes */
    $routes = [
        /** @return mixed */
        static function () use ($base_path, $resources_path, $request) {
            return get($base_path, $resources_path . '/index.php', $request);
        },
        /** @return mixed */
        static function () use ($base_path, $resources_path, $request) {
            return get($base_path . '/create', $resources_path . '/create.php', $request);
        },
        /** @return mixed */
        static function () use ($base_path, $resources_path, $request, $identity_param) {
            return get($base_path . '/{' . $identity_param . '}/edit', $resources_path . '/edit.php', $request);
        },
        /** @return mixed */
        static function () use ($base_path, $resources_path, $request, $identity_param) {
            return get($base_path . '/{' . $identity_param . '}', $resources_path . '/show.php', $request);
        },
        /** @return mixed */
        static function () use ($base_path, $resources_path, $request) {
            return post($base_path, $resources_path . '/store.php', $request);
        },
        /** @return mixed */
        static function () use ($base_path, $resources_path, $request, $identity_param) {
            return put($base_path . '/{' . $identity_param . '}', $resources_path . '/update.php', $request);
        },
        /** @return mixed */
        static function () use ($base_path, $resources_path, $request, $identity_param) {
            return delete($base_path . '/{' . $identity_param . '}', $resources_path . '/destroy.php', $request);
        },
    ];

    /** @var callable(): mixed $route */
    foreach ($routes as $route) {
        /** @var mixed $result */
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
 * @return array{0: string, 1: string}
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
 * @param array{0: string, 1: string}|ServerRequestInterface|null $request
 *
 * @return mixed|null
 */
function files(string $basePath, string $prefix = '', $request = null)
{
    $realpath = realpath($basePath);

    if (false === $realpath) {
        throw new \InvalidArgumentException("{$basePath} does not exists");
    }

    $directory = new \RecursiveDirectoryIterator($realpath);
    $iterator = new \RecursiveIteratorIterator($directory);
    $regex = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

    $files = array_keys(iterator_to_array($regex));

    sort($files);

    $cut = strlen($realpath);
    $prefix = rtrim($prefix, '/');

    foreach ($files as $filename) {
        $cut_filename = substr((string)$filename, $cut);

        if (false === $cut_filename) {
            continue;
        }

        /** @var string $method */
        list($method, $path) = routify($cut_filename);

        if ('/' === $path) {
            if ($prefix) {
                $path = $prefix;
            }
        } else {
            $path = $prefix . $path;
        }

        /** @var mixed|null $result */
        $result = route($method, $path, (string)$filename, $request);

        if ($result !== null) {
            return $result;
        }
    }

    return null;
}

/**
 * Uses a class name to create routes based on its public methods.
 *
 * @param string $basePath The prefix for all routes
 * @param class-string|object $className The qualified class name
 * @param array{0: string, 1: string}|ServerRequestInterface|null $request
 *
 * @return void
 * @throws \ReflectionException
 *
 */
function class_name(string $basePath, $className, $request = null): void
{
    $reflection = new \ReflectionClass($className);
    $object = $reflection->newInstance();

    $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

    foreach ($methods as $method) {
        $specs = preg_split('/(?=[A-Z])/', $method->name);

        $path_segments = array_map('strtolower', array_slice($specs, 1));

        $path_segments = array_filter($path_segments, function (string $segment): bool {
            return $segment != 'index';
        });

        $path_params = array_map(function (\ReflectionParameter $param) {
            return "{{$param->name}}";
        }, $method->getParameters());

        $path_segments = array_merge($path_segments, $path_params);

        array_unshift($path_segments, $basePath);

        route(
            $specs[0],
            join('/', $path_segments),
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
    return boolval(Container\get(CANCEL, false));
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
 * @param array<mixed|null> $routes The route results to br tested
 * @return mixed|null
 */
function match(array $routes)
{
    /** @var mixed|null $route */
    foreach ($routes as $route) {
        if ($route !== null) {
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
    return boolval(Container\get(DID_MATCH, false));
}

/**
 * Invalidate a route match.
 */
function purge_match(): void
{
    Container\set(DID_MATCH, false);
}

/**
 * Defines a base path for all routes.
 *
 * @param string $path
 */
function base(string $path): void
{
    Container\set(BASE_PATH, $path);
}
