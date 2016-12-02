<?php

if (!defined('ENV_PATH')) {
    die('ENV_PATH not defined');
}

$dotenv = new \Dotenv\Dotenv($__bootConfig['env_path']);
$dotenv->load();

function env(string $key = '', $default = null) {
    if (empty($key)) {
        return $_SERVER;
    }

    return array_key_exists($key, $_SERVER) ? $_SERVER[$key] : $default;
}

function dump($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}

function get(string $key, $default = null) {
    return array_key_exists($key, $_GET) ? $_GET[$key] : $default;
}

function post(string $key, $default = null) {
    return array_key_exists($key, $_POST) ? $_POST[$key] : $default;
}

function input(string $key = null, $default = null) {
    if (is_null($key)) {
        return $_REQUEST;
    }

    return array_key_exists($key, $_REQUEST) ? $_REQUEST[$key] : $default;
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
