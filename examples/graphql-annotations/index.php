<?php declare(strict_types=1);

namespace App;

use Siler\GraphQL\DateTimeScalar;
use function Siler\GraphQL\annotated;
use function Siler\GraphQL\debug;
use function Siler\GraphQL\init;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/../../vendor/autoload.php';

$schema = annotated([
    Todo::class,
    TupleInput::class,
    Query::class,
    Mutation::class,
], [
    new DateTimeScalar(),
]);

debug();
init($schema);
