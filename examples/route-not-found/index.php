<?php declare(strict_types=1);

$base_path = realpath(__DIR__ . '/../../');
require_once $base_path . '/vendor/autoload.php';

use Siler\Route;

Route\get('/foo', function () {
    echo 'foo';
});

Route\resource('/api/books', __DIR__ . '/books');

if (!Route\did_match()) {
    http_response_code(404);
    echo 'Not found';
}
