<?php

if (!defined('ENV_PATH')) {
    throw new Exception('ENV_PATH not defined');
}

$dotenv = new \Dotenv\Dotenv(ENV_PATH);
$dotenv->load();

function dump($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}

function __retriver(string $key, $default, array $array) {
    if (empty($key)) {
        return $array;
    }

    return array_key_exists($key, $array) ? $array[$key] : $default;
}

function env(string $key = '', $default = null) {
    return __retriver($key, $default, $_SERVER);
}

function get(string $key = '', $default = null) {
    return __retriver($key, $default, $_GET);
}

function post(string $key = '', $default = null) {
    return __retriver($key, $default, $_POST);
}

function input(string $key = '', $default = null) {
    return __retriver($key, $default, $_REQUEST);
}

function redirect(string $url) {
    header('Location: '.$url);
}

function url(string $path = '/') {
    return rtrim(str_replace('\\', '/', dirname(env('SCRIPT_NAME'))), '/').'/'.ltrim($path, '/');
}

function path() {
    return '/'.ltrim(str_replace(dirname(env('SCRIPT_NAME')), '', env('REQUEST_URI')), '/');
}

function uri(string $protocol = '') {
    if (empty($protocol)) {
        $protocol = empty(env('HTTPS')) ? 'http' : 'https';
    }

    return $protocol.'://'.env('HTTP_HOST').env('REQUEST_URI');
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
    return env('REQUEST_METHOD') == 'POST';
}
