<?php

function dump($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}

function get($key = null, $default = null) {
    return __siler_retriver($key, $default, $_GET);
}

function post($key = null, $default = null) {
    return __siler_retriver($key, $default, $_POST);
}

function input($key = null, $default = null) {
    return __siler_retriver($key, $default, $_REQUEST);
}

function redirect($url) {
    header('Location: '.$url);
}

function url($path = null) {
    if (is_null($path)) {
        $path = '/';
    }

    $scriptName = __siler_retriver('SCRIPT_NAME', '', $_SERVER);
    return rtrim(str_replace('\\', '/', dirname($scriptName)), '/').'/'.ltrim($path, '/');
}

function path() {
    $scriptName = __siler_retriver('SCRIPT_NAME', '', $_SERVER);
    $requestUri = __siler_retriver('REQUEST_URI', '', $_SERVER);

    return '/'.ltrim(str_replace(dirname($scriptName), '', $requestUri), '/');
}

function uri($protocol = null) {
    $https = __siler_retriver('HTTPS', '', $_SERVER);

    if (is_null($protocol)) {
        $protocol = empty($https) ? 'http' : 'https';
    }

    $httpHost = __siler_retriver('HTTP_HOST', '', $_SERVER);
    $requestUri = __siler_retriver('REQUEST_URI', '', $_SERVER);

    return $protocol.'://'.$httpHost.$requestUri;
}

function static_path($path, $callback, $params = []) {
    if ($path == path()) {
        $callback($params);
    }
}

function regexp_path($path, $callback) {
    if (preg_match($path, path(), $params)) {
        $callback($params);
    }
}

function require_fn($filename) {
    return function ($params = []) use ($filename) {
        return require($filename);
    };
}

function is_post() {
    return request_method_is('post');
}

function is_get() {
    return request_method_is('get');
}

function is_put() {
    return request_method_is('put');
}

function is_delete() {
    return request_method_is('delete');
}

function request_method_is($method) {
    $requestMethod = __siler_retriver('REQUEST_METHOD', 'GET', $_SERVER);
    return strtolower($method) == strtolower($requestMethod);
}

function __siler_retriver($key, $default, $array) {
    if (is_null($key)) {
        return $array;
    }

    return array_key_exists($key, $array) ? $array[$key] : $default;
}
