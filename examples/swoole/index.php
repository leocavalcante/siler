<?php

declare(strict_types=1);
require_once __DIR__.'/../../vendor/autoload.php';

use Siler\Route;
use Siler\Swoole;
use Siler\Twig;

Twig\init(__DIR__.'/pages');

$handler = function () {
    Route\get('/', __DIR__.'/pages/home.php');
    Route\get('/todos', __DIR__.'/api/todos.php');

    // None of the above short-circuited the response with Swoole\emit().
    Swoole\emit('Not found', 404);
};

Swoole\start(9501)($handler);
