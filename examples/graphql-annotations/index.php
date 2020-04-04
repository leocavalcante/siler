<?php declare(strict_types=1);

namespace App;

use Siler\GraphQL\DateTimeScalar;
use function Siler\GraphQL\{debug, init};
use function Siler\GraphQL\Annotation\annotated;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/../../vendor/autoload.php';

$schema = annotated([
    Foo::class,
    Bar::class,
    FooBar::class,
    TodoStatus::class,
    ITodo::class,
    Todo::class,
    TupleInput::class,
    Query::class,
    Mutation::class,
], [
    new DateTimeScalar(),
]);

debug();
init($schema);
