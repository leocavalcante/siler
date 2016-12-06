<?php

function dump($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}

function __siler_retriver(string $key, $default, array $array) {
    if (empty($key)) {
        return $array;
    }

    return array_key_exists($key, $array) ? $array[$key] : $default;
}

function get(string $key = '', $default = null) {
    return __siler_retriver($key, $default, $_GET);
}

function post(string $key = '', $default = null) {
    return __siler_retriver($key, $default, $_POST);
}

function input(string $key = '', $default = null) {
    return __siler_retriver($key, $default, $_REQUEST);
}

function redirect(string $url) {
    header('Location: '.$url);
}

function url(string $path = '/') {
    $scriptName = __siler_retriver('SCRIPT_NAME', '', $_SERVER);
    return rtrim(str_replace('\\', '/', dirname($scriptName)), '/').'/'.ltrim($path, '/');
}

function path() {
    $scriptName = __siler_retriver('SCRIPT_NAME', '', $_SERVER);
    $requestUri = __siler_retriver('REQUEST_URI', '', $_SERVER);

    return '/'.ltrim(str_replace(dirname($scriptName), '', $requestUri), '/');
}

function uri(string $protocol = '') {
    $https = __siler_retriver('HTTPS', '', $_SERVER);

    if (empty($protocol)) {
        $protocol = empty($https) ? 'http' : 'https';
    }

    $httpHost = __siler_retriver('HTTP_HOST', '', $_SERVER);
    $requestUri = __siler_retriver('REQUEST_URI', '', $_SERVER);

    return $protocol.'://'.$httpHost.$requestUri;
}

function static_path(string $path, callable $callback, array $params = []) {
    if ($path == path()) {
        $callback($params);
    }
}

function regexp_path(string $path, callable $callback) {
    if (preg_match($path, path(), $params)) {
        $callback($params);
    }
}

function require_fn(string $filename) {
    return function (array $params = []) use ($filename) {
        return require($filename);
    };
}

function is_post() {
    $requestMethod = __siler_retriver('REQUEST_METHOD', 'GET', $_SERVER);
    return 'post' == strtolower($requestMethod);
}

function is_get() {
    $requestMethod = __siler_retriver('REQUEST_METHOD', 'GET', $_SERVER);
    return 'get' == strtolower($requestMethod);
}

function is_put() {
    $requestMethod = __siler_retriver('REQUEST_METHOD', 'GET', $_SERVER);
    return 'put' == strtolower($requestMethod);
}

function is_delete() {
    $requestMethod = __siler_retriver('REQUEST_METHOD', 'GET', $_SERVER);
    return 'delete' == strtolower($requestMethod);
}

function request_method_is(string $method) {
    $requestMethod = __siler_retriver('REQUEST_METHOD', 'GET', $_SERVER);
    return strtolower($method) == strtolower($requestMethod);
}
