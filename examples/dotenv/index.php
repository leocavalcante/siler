<?php

use Siler\Dotenv;
use Siler\Route;

chdir(dirname(dirname(__DIR__)));
require 'vendor/autoload.php';

Dotenv\init('examples/dotenv/');

Route\get(
    '/',
    function () {
        echo Dotenv\env('MESSAGE');
    }
);
