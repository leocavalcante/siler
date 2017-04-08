<?php

use Siler\Route;

chdir(dirname(dirname(__DIR__)));
require 'vendor/autoload.php';

Route\get('/', function () {
    echo 'Hello World!';
});

Route\get('/hello/{name}', function ($params) {
    printf('Hello %s', $params['name']);
});

Route\get('/hello-world/{name}', 'examples/hello-world/hello-world.phtml');

Route\get('/functional-hello', λ\puts('Hello Functional World'));
