<?php

namespace Siler\Dotenv;

function init($path) {
    $dotenv = new \Dotenv\Dotenv($path);
    return $dotenv->load();
}

function env($key = null, $default = null) {
    return \Siler\__retriver($key, $default, $_SERVER);
}
