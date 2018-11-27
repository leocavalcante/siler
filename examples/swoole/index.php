<?php

declare(strict_types=1);
require __DIR__.'/../../vendor/autoload.php';

use Siler\Functional as F;
use Siler\Route;
use Siler\Swoole;

Swoole\handle(function ($req, $res) {
    $req = Swoole\wrap($req);

    Route\get('/', F\puts('Hello World'), $req);
});

Swoole\start('0.0.0.0', 9502);
