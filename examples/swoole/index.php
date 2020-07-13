<?php declare(strict_types=1);

use Siler\Route;
use Siler\Swoole;
use Siler\Twig;

require_once __DIR__ . '/../../vendor/autoload.php';

Twig\init(__DIR__ . '/pages');

$handler = function () {
    Route\get('/', __DIR__ . '/pages/home.php');
    Route\get('/todos', __DIR__ . '/api/todos.php');

    // None of the above short-circuited the response with Swoole\emit().
    Swoole\emit('Not found', 404);
};

$server = Swoole\http($handler);
$server->set([
    'enable_static_handler' => true,
    'document_root' => __DIR__ . '/public',
]);
$server->start();
