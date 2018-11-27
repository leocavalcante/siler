<?php

declare(strict_types=1);
require __DIR__.'/../../vendor/autoload.php';

use Siler\Swoole;
use Siler\Route;
use Siler\Functional as F;

Swoole\handle(function ($req, $res) {
    $req = Swoole\wrap($req);

    Route\get('/', F\puts('Hello World'), $req);
});

Swoole\start('0.0.0.0', 9502);
