<?php

namespace Siler;

function dump($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}

function get($key = null, $default = null) {
    return array_get($_GET, $key, $default);
}

function post($key = null, $default = null) {
    return array_get($_POST, $key, $default);
}

function input($key = null, $default = null) {
    return array_get($_REQUEST, $key, $default);
}

function redirect($url) {
    header('Location: '.$url);
}

function url($path = null) {
    if (is_null($path)) {
        $path = '/';
    }

    $scriptName = array_get($_SERVER, 'SCRIPT_NAME', '');
    return rtrim(str_replace('\\', '/', dirname($scriptName)), '/').'/'.ltrim($path, '/');
}

function path() {
    $scriptName = array_get($_SERVER, 'SCRIPT_NAME', '');
    $requestUri = array_get($_SERVER, 'REQUEST_URI', '');

    return '/'.ltrim(str_replace(dirname($scriptName), '', $requestUri), '/');
}

function uri($protocol = null) {
    $https = array_get($_SERVER, 'HTTPS', '');

    if (is_null($protocol)) {
        $protocol = empty($https) ? 'http' : 'https';
    }

    $httpHost = array_get($_SERVER, 'HTTP_HOST', '');
    $requestUri = array_get($_SERVER, 'REQUEST_URI', '');

    return $protocol.'://'.$httpHost.$requestUri;
}

function route($path, $callback) {
    if (preg_match($path, path(), $params)) {
        $callback($params);
    }
}

function require_fn($filename) {
    return function ($params = null) use ($filename) {
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

function is_options() {
    return request_method_is('options');
}

function request_method_is($method) {
    $requestMethod = array_get($_SERVER, 'REQUEST_METHOD', 'GET');
    return strtolower($method) == strtolower($requestMethod);
}

function array_get($array, $key = null, $default = null) {
    if (is_null($key)) {
        return $array;
    }

    return array_key_exists($key, $array) ? $array[$key] : $default;
}
