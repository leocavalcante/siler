<?php declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Siler\Functional as λ; // Just to be cool, don't use non-ASCII identifiers ;)

$pipeline = λ\pipe([
    λ\lmap(fn(string $s): string => trim($s)),
    λ\lfilter(λ\not(λ\equal('baz'))),
    λ\non_empty,
    λ\ljoin(',')
]);

echo $pipeline(['foo', ' ', 'bar', 'baz']); // foo,bar
