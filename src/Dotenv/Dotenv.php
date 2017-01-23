<?php

namespace Siler\Dotenv;

function init($path)
{
    $dotenv = new \Dotenv\Dotenv($path);
    return $dotenv->load();
}

function env($key = null, $default = null)
{
    return \Siler\array_get($_SERVER, $key, $default);
}
