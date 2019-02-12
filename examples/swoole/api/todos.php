<?php

declare(strict_types=1);

use Siler\Swoole;

$req = Swoole\request();
$res = Swoole\response();

$todos = [
    [
        'id'   => 1,
        'text' => 'foo',
    ],
    [
        'id'   => 2,
        'text' => 'bar',
    ],
    [
        'id'   => 3,
        'text' => 'baz',
    ],
];

Swoole\emit(json_encode($todos), 200, ['Content-Type' => 'application/json']);
