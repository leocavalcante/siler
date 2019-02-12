<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Siler\Functional as λ;

$nameOf = λ\match(
    [
        [
            λ\equal(1),
            λ\always('one'),
        ],
        [
            λ\equal(2),
            λ\always('two'),
        ],
        [
            λ\equal(3),
            λ\always('three'),
        ],
    ]
);

echo $nameOf(1);
// one
echo $nameOf(2);
// two
echo $nameOf(3);
// three
